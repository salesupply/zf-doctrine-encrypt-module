<?php

namespace ZfDoctrineEncryptModule;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface, AutoloaderProviderInterface
{
    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];

        foreach (glob(__DIR__ . '/../config/*.config.php') as $filename) {
            $config = array_merge_recursive($config, include $filename);
        }

        return $config;
    }

    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . DIRECTORY_SEPARATOR . 'src',
                ],
            ],
        ];
    }
}