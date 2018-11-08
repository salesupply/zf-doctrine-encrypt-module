<?php

namespace ZfDoctrineEncryptModule\Factory\Service;

use DoctrineEncrypt\Encryptors\EncryptorInterface;
use Exception;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZfDoctrineEncryptModule\Service\EncryptManager;

class EncryptManagerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return object|EncryptManager
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');

        if (!isset($config['doctrine']['encryption']['orm_default'])) {

            throw new Exception(
                sprintf('Could not find encryption config in %s to create %s.', __CLASS__, EncryptManager::class)
            );
        }

        /** @var EncryptorInterface $adapter */
        $adapter = $container->build(
            'encryption_adapter',
            [
                'key' => $config['doctrine']['encryption']['orm_default']['key'],
            ]
        );

        return new EncryptManager($adapter);
    }
}