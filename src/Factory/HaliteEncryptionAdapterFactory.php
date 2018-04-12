<?php

namespace ZfDoctrineEncryptModule\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZfDoctrineEncryptModule\Adapter\HaliteEncryptionAdapter;
use ZfDoctrineEncryptModule\Exception\OptionsNotFoundException;

class HaliteEncryptionAdapterFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return HaliteEncryptionAdapter
     * @throws OptionsNotFoundException
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \TypeError
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (!is_array($options) || empty($options)) {

            throw new OptionsNotFoundException('Options required to be set in the config for HaliteAdapter are "key".');
        }

        if (!key_exists('key', $options) && !is_string($options['key'])) {

            throw new OptionsNotFoundException('Option "key" is required.');
        }

        return new HaliteEncryptionAdapter($options['key']);
    }
}