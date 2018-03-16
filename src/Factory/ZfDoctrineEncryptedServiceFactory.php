<?php

namespace ZfDoctrineEncryptModule\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use DoctrineEncrypt\Encryptors\EncryptorInterface;
use DoctrineEncrypt\Subscribers\DoctrineEncryptSubscriber;
use DoctrineModule\Service\AbstractFactory;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfDoctrineEncryptModule\Adapter\HaliteAdapter;
use ZfDoctrineEncryptModule\Options\ModuleOptions;

class ZfDoctrineEncryptedServiceFactory extends AbstractFactory
{
    /**
     * @param ServiceLocatorInterface $container
     * @return DoctrineEncryptSubscriber
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function createService(ServiceLocatorInterface $container)
    {
        /** @var ModuleOptions $options */
        $options = $this->getOptions($container, 'encryption');

        /** @var Reader|AnnotationReader $reader */
        $reader = $this->createReader($container, $options->getReader());
        /** @var EncryptorInterface|HaliteAdapter $adapter */
        $adapter = $this->createAdapter(
            $container,
            $options->getAdapter(),
            [
                'key' => $options->getKey(),
                'salt' => $options->getSalt(),
            ]
        );

        return new DoctrineEncryptSubscriber(
            $reader,
            $adapter
        );
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return DoctrineEncryptSubscriber
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $this->createService($container);
    }

    /**
     * Get the class name of the options associated with this factory.
     *
     * @return string
     */
    public function getOptionsClass()
    {
        return ModuleOptions::class;
    }

    /**
     * @param ContainerInterface $container
     * @param string $reader
     * @param array|null $options
     * @return Reader
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function createReader(ContainerInterface $container, string $reader, array $options = null)
    {
        /** @var Reader $reader */
        $reader = $this->hydrateDefinition($reader, $container, $options);

        if (!$reader instanceof Reader) {

            throw new \InvalidArgumentException(
                'Invalid reader provided. Must implement ' . Reader::class
            );
        }

        return $reader;
    }

    /**
     * @param ContainerInterface $container
     * @param $adapter
     * @param array|null $options
     * @return EncryptorInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function createAdapter(ContainerInterface $container, string $adapter, array $options = null)
    {
        /** @var EncryptorInterface $adapter */
        $adapter = $this->hydrateDefinition($adapter, $container, $options);

        if (!$adapter instanceof EncryptorInterface) {
            throw new \InvalidArgumentException(
                'Invalid encryptor provided, must be a service name, '
                . 'class name, an instance, or method returning an ' . EncryptorInterface::class
            );
        }

        return $adapter;
    }

    /**
     * Hydrates the value into an object
     *
     * @param $value
     * @param ContainerInterface $container
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function hydrateDefinition($value, ContainerInterface $container, array $options = null)
    {
        if (is_string($value)) {
            if ($container->has($value)) {
                if (is_array($options)) {
                    $value = $container->build($value, $options);
                } else {
                    $value = $container->get($value);
                }
            } elseif (class_exists($value)) {
                $value = new $value();
            }
        } elseif (is_callable($value)) {
            $value = $value();
        }

        return $value;
    }
}