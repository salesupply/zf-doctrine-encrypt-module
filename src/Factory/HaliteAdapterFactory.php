<?php

namespace ZfDoctrineEncryptModule\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZfDoctrineEncryptModule\Adapter\HaliteAdapter;
use ZfDoctrineEncryptModule\Exception\OptionsNotFoundException;

class HaliteAdapterFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object|HaliteAdapter
     * @throws OptionsNotFoundException
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \TypeError
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (!is_array($options) || empty($options)) {

            throw new OptionsNotFoundException('Options required to be set in the config for HaliteAdapter are "key" and "salt".');
        }

        if (!key_exists('key', $options) && !is_string($options['key'])) {

            throw new OptionsNotFoundException('Option "key" is required.');
        }

        if (!key_exists('salt', $options) && !is_string($options['salt'])) {

            throw new OptionsNotFoundException('Option "salt" is required.');
        }

        return new HaliteAdapter($options['key'], $options['salt']);
    }
}