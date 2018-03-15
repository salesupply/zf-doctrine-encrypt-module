<?php

namespace ZfDoctrineEncryptModule;

use Doctrine\Common\Annotations\AnnotationReader;
use ZfDoctrineEncryptModule\Adapter\HaliteAdapter;
use ZfDoctrineEncryptModule\Factory\HaliteAdapterFactory;
use ZfDoctrineEncryptModule\Factory\ZfDoctrineEncryptedServiceFactory;

return [
    'doctrine_factories' => [
        'encryption' => ZfDoctrineEncryptedServiceFactory::class,
    ],
    'doctrine' => [
        'encryption' => [
            'orm_default' => [
                'adapter' => 'encryption_adapter',
                'reader' => AnnotationReader::class,
            ],
        ],
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    'doctrine.encryption.orm_default',
                ],
            ],
        ],
    ],
    'service_manager' => [
        'aliases' => [
            'encryption_adapter' => HaliteAdapter::class,
        ],
        'factories' => [
            // Using aliases so someone else can use own adapter/factory
            HaliteAdapter::class => HaliteAdapterFactory::class
        ],
    ],
];