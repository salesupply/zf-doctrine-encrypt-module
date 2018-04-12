<?php

namespace ZfDoctrineEncryptModule;

use Doctrine\Common\Annotations\AnnotationReader;
use ZfDoctrineEncryptModule\Adapter\HaliteEncryptionAdapter;
use ZfDoctrineEncryptModule\Factory\HaliteEncryptionAdapterFactory;
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
            'encryption_adapter' => HaliteEncryptionAdapter::class,
        ],
        'factories' => [
            // Using aliases so someone else can use own adapter/factory
            HaliteEncryptionAdapter::class => HaliteEncryptionAdapterFactory::class
        ],
    ],
];