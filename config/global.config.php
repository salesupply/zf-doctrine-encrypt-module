<?php

namespace ZfDoctrineEncryptModule;

use Doctrine\Common\Annotations\AnnotationReader;
use ZfDoctrineEncryptModule\Adapter\HaliteEncryptionAdapter;
use ZfDoctrineEncryptModule\Adapter\HaliteHashingAdapter;
use ZfDoctrineEncryptModule\Factory\HaliteEncryptionAdapterFactory;
use ZfDoctrineEncryptModule\Factory\HaliteHashingAdapterFactory;
use ZfDoctrineEncryptModule\Factory\Service\EncryptManagerFactory;
use ZfDoctrineEncryptModule\Factory\Service\HashManagerFactory;
use ZfDoctrineEncryptModule\Factory\ZfDoctrineEncryptedServiceFactory;
use ZfDoctrineEncryptModule\Factory\ZfDoctrineHashedServiceFactory;
use ZfDoctrineEncryptModule\Service\EncryptManager;
use ZfDoctrineEncryptModule\Service\HashManager;

return [
    'doctrine_factories' => [
        'encryption' => ZfDoctrineEncryptedServiceFactory::class,
        'hashing' => ZfDoctrineHashedServiceFactory::class,
    ],
    'doctrine' => [
        'encryption' => [
            'orm_default' => [
                'adapter' => 'encryption_adapter',
                'reader' => AnnotationReader::class,
            ],
        ],
        'hashing' => [
            'orm_default' => [
                'adapter' => 'hashing_adapter',
                'reader' => AnnotationReader::class,
            ],
        ],
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    'doctrine.encryption.orm_default',
                    'doctrine.hashing.orm_default',
                ],
            ],
        ],
    ],
    'service_manager' => [
        'aliases' => [
            'encryption_adapter' => HaliteEncryptionAdapter::class,
            'hashing_adapter'    => HaliteHashingAdapter::class,
            'encryption_service' => EncryptManager::class,
            'hashing_service'    => HashManager::class,
        ],
        'factories' => [
            // Using aliases so someone else can use own adapter/factory
            HaliteEncryptionAdapter::class => HaliteEncryptionAdapterFactory::class,
            HaliteHashingAdapter::class    => HaliteHashingAdapterFactory::class,
            EncryptManager::class          => EncryptManagerFactory::class,
            HashManager::class             => HashManagerFactory::class,
        ],
    ],
];