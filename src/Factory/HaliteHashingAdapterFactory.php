<?php

namespace ZfDoctrineEncryptModule\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZfDoctrineEncryptModule\Adapter\HaliteHashingAdapter;
use ZfDoctrineEncryptModule\Exception\OptionsNotFoundException;

class HaliteHashingAdapterFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return HaliteHashingAdapter
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

        if (!key_exists('pepper', $options) && !is_string($options['pepper'])) {

            throw new OptionsNotFoundException('Option "pepper" is required.');
        }

        return new HaliteHashingAdapter($options['key'], $options['pepper']);
    }
}