<?php

namespace ZfDoctrineEncryptModule\Subscriber;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use DoctrineEncrypt\Encryptors\EncryptorInterface;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ZfDoctrineEncryptModule\Annotation\Encrypted;
use ZfDoctrineEncryptModule\Interfaces\PepperInterface;
use ZfDoctrineEncryptModule\Interfaces\SaltInterface;
use ZfDoctrineEncryptModule\Interfaces\SpicyInterface;

class DoctrineEncryptSubscriber implements EventSubscriber
{
    /**
     * Encryptor interface namespace
     */
    const ENCRYPTOR_INTERFACE_NS = EncryptorInterface::class;

    /**
     * Encrypted annotation full name
     */
    const ENCRYPTED_ANN_NAME = Encrypted::class;

    /**
     * Encryptor
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * Annotation reader
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $reader;

    /**
     * Register to avoid multi decode operations for one entity
     * @var array
     */
    private $decodedRegistry = [];

    /**
     * Caches information on an entity's encrypted fields in an array keyed on
     * the entity's class name. The value will be a list of Reflected fields that are encrypted.
     *
     * @var array
     */
    private $encryptedFieldCache = [];

    /**
     * Before flushing the objects out to the database, we modify their password value to the
     * encrypted value. Since we want the password to remain decrypted on the entity after a flush,
     * we have to write the decrypted value back to the entity.
     * @var array
     */
    private $postFlushDecryptQueue = [];

    /**
     * DoctrineEncryptSubscriber constructor.
     * @param Reader $reader
     * @param EncryptorInterface $encryptor
     */
    public function __construct(Reader $reader, EncryptorInterface $encryptor)
    {
        $this->setReader($reader);
        $this->setEncryptor($encryptor);
    }

    /**
     * Encrypt the password before it is written to the database.
     *
     * Notice that we do not recalculate changes otherwise the password will be written
     * every time (Because it is going to differ from the un-encrypted value)
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $unitOfWork = $em->getUnitOfWork();

        $this->postFlushDecryptQueue = [];

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $this->entityOnFlush($entity, $em);
            $unitOfWork->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($entity)), $entity);
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->entityOnFlush($entity, $em);
            $unitOfWork->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($entity)), $entity);
        }
    }


    /**
     * Processes the entity for an onFlush event.
     *
     * @param object $entity
     * @param EntityManager $em
     */
    private function entityOnFlush($entity, EntityManager $em)
    {
        $objId = spl_object_hash($entity);

        $fields = [];

        foreach ($this->getEncryptedFields($entity, $em) as $field) {
            $fields[$field['reflection']->getName()] = [
                'field' => $field['reflection'],
                'value' => $field['reflection']->getValue($entity),
                'options' => $field['options'],
            ];
        }

        $this->postFlushDecryptQueue[$objId] = [
            'entity' => $entity,
            'fields' => $fields,
        ];

        $this->processFields($entity, $em);
    }

    /**
     * After we have persisted the entities, we want to have the
     * decrypted information available once more.
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $unitOfWork = $args->getEntityManager()->getUnitOfWork();

        foreach ($this->postFlushDecryptQueue as $pair) {
            $fieldPairs = $pair['fields'];
            $entity = $pair['entity'];
            $oid = spl_object_hash($entity);

            foreach ($fieldPairs as $fieldPair) {
                /** @var \ReflectionProperty $field */
                $field = $fieldPair['field'];

                $field->setValue($entity, $fieldPair['value']);
                $unitOfWork->setOriginalEntityProperty($oid, $field->getName(), $fieldPair['value']);
            }

            $this->addToDecodedRegistry($entity);
        }

        $this->postFlushDecryptQueue = [];
    }

    /**
     * Listen a postLoad lifecycle event. Checking and decrypt entities
     * which have @Encrypted annotations
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if (! $this->hasInDecodedRegistry($entity)) {
            if ($this->processFields($entity, $em, false)) {
                $this->addToDecodedRegistry($entity);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postLoad,
            Events::onFlush,
            Events::postFlush,
        ];
    }

    public static function capitalize(string $word): string
    {
        if (is_array($word)) {
            $word = $word[0];
        }

        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $word)));
    }

    /**
     * Process (encrypt/decrypt) entities fields
     *
     * @param $entity
     * @param EntityManager $em
     * @param bool $isEncryptOperation
     * @return bool
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Exception
     */
    private function processFields($entity, EntityManager $em, $isEncryptOperation = true): bool
    {
        $properties = $this->getEncryptedFields($entity, $em);

        $unitOfWork = $em->getUnitOfWork();
        $oid = spl_object_hash($entity);

        foreach ($properties as $property) {
            /** @var \ReflectionProperty $refProperty */
            $refProperty = $property['reflection'];
            /** @var Encrypted $annotationOptions */
            $annotationOptions = $property['options'];
            /** @var boolean $nullable */
            $nullable = $property['nullable'];

            $value = $refProperty->getValue($entity);
            // If the value is 'null' && is nullable, don't do anything, just skip it.
            if (is_null($value) && $nullable) {

                continue;
            }

            $value = $isEncryptOperation ?
                $this->encrypt($entity, $value, $annotationOptions) :
                $this->decrypt($entity, $value, $annotationOptions);

            $type = $annotationOptions->getType();

            // If NOT encrypting, type know to PHP and the value does not match the type. Else error
            if (
                $isEncryptOperation === false
                // We're going to try a cast using settype. Array of types defined at: https://php.net/settype
                && in_array($type, ['boolean', 'bool', 'integer', 'int', 'float', 'double', 'string', 'array', 'object', 'null'])
                && gettype($value) !== $type
            ) {
                if (settype($value, $type) === false) {

                    throw new \Exception(
                        'Could not convert encrypted value back to mapped value in ' . __CLASS__ . '::' . __FUNCTION__ . PHP_EOL
                    );
                }
            }

            $refProperty->setValue($entity, $value);

            if (! $isEncryptOperation) {
                //we don't want the object to be dirty immediately after reading
                $unitOfWork->setOriginalEntityProperty($oid, $refProperty->getName(), $value);
            }
        }

        return ! empty($properties);
    }

    private function encrypt($entity, $value, Encrypted $options)
    {
        // Check if option 'spices' is set. If so, expect a related Entity on $entity for get{$options->getSpices()}()
        // Use related Entity to get Salt & Pepper. getSalt() and getPepper() should exist due to implementation of SpicyInterface
        if (
            !is_null($options->getSpices())
            && method_exists($entity, 'get' . ucfirst($options->getSpices()))
            && $entity->{'get' . ucfirst($options->getSpices())}() instanceof SpicyInterface
            && ($salt = $entity->{'get' . ucfirst($options->getSpices())}()->getSalt())
            && ($pepper = $entity->{'get' . ucfirst($options->getSpices())}()->getPepper())
        ) {

            return $this->getEncryptor()->encrypt($salt . $value . $pepper);
        }

        // Check if option 'salt' is set and 'spices' and 'pepper' options are not set.
        // If so, expect a related Entity on $entity for get{$options->getSalt()}()
        // Use related Entity to get Salt. getSalt() should exist due to implementation of SaltInterface
        if (
            is_null($options->getSpices())
            && is_null($options->getPepper())
            && !is_null($options->getSalt())
            && method_exists($entity, 'get' . ucfirst($options->getSalt()))
            && $entity->{'get' . ucfirst($options->getSalt())}() instanceof SaltInterface
            && ($salt = $entity->{'get' . ucfirst($options->getSalt())}()->getSalt())
        ) {

            return $this->getEncryptor()->encrypt($salt . $value);
        }

        // Check if option 'pepper' is set and 'spices' && salt options are not set.
        // If so, expect a related Entity on $entity for get{$options->getPepper()}()
        // Use related Entity to get Salt. getPepper() should exist due to implementation of PepperInterface
        if (
            is_null($options->getSpices())
            && is_null($options->getSalt())
            && !is_null($options->getPepper())
            && method_exists($entity, 'get' . ucfirst($options->getPepper()))
            && $entity->{'get' . ucfirst($options->getPepper())}() instanceof PepperInterface
            && ($pepper = $entity->{'get' . ucfirst($options->getPepper())}()->getPepper())
        ) {

            return $this->getEncryptor()->encrypt($value . $pepper);
        }

        // Check if options 'salt' and 'pepper' are set and 'spices' is not set.
        // If so, expect a related Entity on $entity for get{$options->getPepper()}()
        // Use related Entity to get Salt. getPepper() should exist due to implementation of PepperInterface
        if (
            is_null($options->getSpices())
            && !is_null($options->getSalt())
            && !is_null($options->getPepper())
            && method_exists($entity, 'get' . ucfirst($options->getSalt()))
            && method_exists($entity, 'get' . ucfirst($options->getPepper()))
            && $entity->{'get' . ucfirst($options->getSalt())}() instanceof SaltInterface
            && $entity->{'get' . ucfirst($options->getPepper())}() instanceof PepperInterface
            && ($salt = $entity->{'get' . ucfirst($options->getSalt())}()->getSalt())
            && ($pepper = $entity->{'get' . ucfirst($options->getPepper())}()->getPepper())
        ) {

            return $this->getEncryptor()->encrypt($salt . $value . $pepper);
        }

        return $this->getEncryptor()->encrypt($value);
    }

    private function decrypt($entity, $value, Encrypted $options)
    {
        $decrypted = $this->getEncryptor()->decrypt($value);

        // Check if option 'spices' is set.
        // If so, expect a related Entity on $entity for get{$options->getSpices()}()
        // Use related Entity to get Salt & Pepper. getSalt() and getPepper() should exist due to implementation of SpicyInterface
        if (
            !is_null($options->getSpices())
            && method_exists($entity, 'get' . ucfirst($options->getSpices()))
            && $entity->{'get' . ucfirst($options->getSpices())}() instanceof SpicyInterface
            && ($salt = $entity->{'get' . ucfirst($options->getSpices())}()->getSalt())
            && ($pepper = $entity->{'get' . ucfirst($options->getSpices())}()->getPepper())
        ) {

            return str_replace($pepper, '', str_replace($salt, '', $decrypted));
        }

        // Check if option 'salt' is set and 'spices' and 'pepper' options are not set.
        // If so, expect a related Entity on $entity for get{$options->getSalt()}()
        // Use related Entity to get Salt. getSalt() should exist due to implementation of SaltInterface
        if (
            is_null($options->getSpices())
            && is_null($options->getPepper())
            && !is_null($options->getSalt())
            && method_exists($entity, 'get' . ucfirst($options->getSalt()))
            && $entity->{'get' . ucfirst($options->getSalt())}() instanceof SaltInterface
            && ($salt = $entity->{'get' . ucfirst($options->getSalt())}()->getSalt())
        ) {

            return str_replace($salt, '', $decrypted);
        }

        // Check if option 'pepper' is set and 'spices' && salt options are not set.
        // If so, expect a related Entity on $entity for get{$options->getPepper()}()
        // Use related Entity to get Salt. getPepper() should exist due to implementation of PepperInterface
        if (
            is_null($options->getSpices())
            && is_null($options->getSalt())
            && !is_null($options->getPepper())
            && method_exists($entity, 'get' . ucfirst($options->getPepper()))
            && $entity->{'get' . ucfirst($options->getPepper())}() instanceof PepperInterface
            && ($pepper = $entity->{'get' . ucfirst($options->getPepper())}()->getPepper())
        ) {

            return str_replace($pepper, '', $decrypted);
        }

        // Check if options 'salt' and 'pepper' are set and 'spices' is not set.
        // If so, expect a related Entity on $entity for get{$options->getPepper()}()
        // Use related Entity to get Salt. getPepper() should exist due to implementation of PepperInterface
        if (
            is_null($options->getSpices())
            && !is_null($options->getSalt())
            && !is_null($options->getPepper())
            && method_exists($entity, 'get' . ucfirst($options->getSalt()))
            && method_exists($entity, 'get' . ucfirst($options->getPepper()))
            && $entity->{'get' . ucfirst($options->getSalt())}() instanceof SaltInterface
            && $entity->{'get' . ucfirst($options->getPepper())}() instanceof PepperInterface
            && ($salt = $entity->{'get' . ucfirst($options->getSalt())}()->getSalt())
            && ($pepper = $entity->{'get' . ucfirst($options->getPepper())}()->getPepper())
        ) {

            return str_replace($pepper, '', str_replace($salt, '', $decrypted));
        }

        return $decrypted;
    }

    /**
     * Check if we have entity in decoded registry
     *
     * @param object $entity Some doctrine entity
     * @return bool
     */
    private function hasInDecodedRegistry($entity): bool
    {
        return isset($this->decodedRegistry[spl_object_hash($entity)]);
    }

    /**
     * Adds entity to decoded registry
     *
     * @param object $entity Some doctrine entity
     */
    private function addToDecodedRegistry($entity)
    {
        $this->decodedRegistry[spl_object_hash($entity)] = true;
    }

    /**
     * @param object $entity
     * @param EntityManager $em
     * @return array|mixed
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    private function getEncryptedFields(object $entity, EntityManager $em)
    {
        $className = get_class($entity);

        if (isset($this->encryptedFieldCache[$className])) {
            return $this->encryptedFieldCache[$className];
        }

        $meta = $em->getClassMetadata($className);

        $encryptedFields = [];

        foreach ($meta->getReflectionProperties() as $refProperty) {
            /** @var \ReflectionProperty $refProperty */

            // Gets Encrypted object from property Annotation. Includes options and their values.
            $annotationOptions = $this->reader->getPropertyAnnotation($refProperty, $this::ENCRYPTED_ANN_NAME) ?: [];

            if (!empty($annotationOptions)) {
                $refProperty->setAccessible(true);
                $encryptedFields[] = [
                    'reflection' => $refProperty,
                    'options' => $annotationOptions,
                    'nullable' => $meta->getFieldMapping($refProperty->getName())['nullable'],
                ];
            }
        }

        $this->encryptedFieldCache[$className] = $encryptedFields;

        return $encryptedFields;
    }

    /**
     * @return EncryptorInterface
     */
    public function getEncryptor(): EncryptorInterface
    {
        return $this->encryptor;
    }

    /**
     * @param EncryptorInterface $encryptor
     * @return DoctrineEncryptSubscriber
     */
    public function setEncryptor(EncryptorInterface $encryptor): DoctrineEncryptSubscriber
    {
        $this->encryptor = $encryptor;
        return $this;
    }

    /**
     * @return Reader
     */
    public function getReader(): Reader
    {
        return $this->reader;
    }

    /**
     * @param Reader $reader
     * @return DoctrineEncryptSubscriber
     */
    public function setReader(Reader $reader): DoctrineEncryptSubscriber
    {
        $this->reader = $reader;
        return $this;
    }

    /**
     * @return array
     */
    public function getDecodedRegistry(): array
    {
        return $this->decodedRegistry;
    }

    /**
     * @param array $decodedRegistry
     * @return DoctrineEncryptSubscriber
     */
    public function setDecodedRegistry(array $decodedRegistry): DoctrineEncryptSubscriber
    {
        $this->decodedRegistry = $decodedRegistry;
        return $this;
    }

    /**
     * @return array
     */
    public function getEncryptedFieldCache(): array
    {
        return $this->encryptedFieldCache;
    }

    /**
     * @param array $encryptedFieldCache
     * @return DoctrineEncryptSubscriber
     */
    public function setEncryptedFieldCache(array $encryptedFieldCache): DoctrineEncryptSubscriber
    {
        $this->encryptedFieldCache = $encryptedFieldCache;
        return $this;
    }

    /**
     * @return array
     */
    public function getPostFlushDecryptQueue(): array
    {
        return $this->postFlushDecryptQueue;
    }

    /**
     * @param array $postFlushDecryptQueue
     * @return DoctrineEncryptSubscriber
     */
    public function setPostFlushDecryptQueue(array $postFlushDecryptQueue): DoctrineEncryptSubscriber
    {
        $this->postFlushDecryptQueue = $postFlushDecryptQueue;
        return $this;
    }
}