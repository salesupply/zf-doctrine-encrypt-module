<?php

namespace ZfDoctrineEncryptModule\Factory\Service;

use Exception;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZfDoctrineEncryptModule\Interfaces\HashInterface;
use ZfDoctrineEncryptModule\Service\HashManager;

class HashManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');

        if ( ! isset($config['doctrine']['hashing']['orm_default'])) {

            throw new Exception(
                sprintf('Could not find hashing config in %s to create %s.', __CLASS__, HashManager::class)
            );
        }

        /** @var HashInterface $adapter */
        $adapter = $container->build(
            'hashing_adapter',
            [
                'key'    => $config['doctrine']['hashing']['orm_default']['key'],
                'pepper' => $config['doctrine']['hashing']['orm_default']['pepper'],
            ]
        );

        return new HashManager($adapter);
    }
}