<?php

namespace ZfDoctrineEncryptModule;

return [
    'doctrine_factories' => [
        'encryption' => '', // FQCN to DoctrineEncryptionFactory or something
    ],
    'doctrine' => [
        'encryption' => [
            'orm_default' => [
                'adapter' => '', // FQCN to EncryptionAdapter or somethign
                'reader' => '', // FQCN to AnnotationReader
            ],
            'key' => '',
            'salt' => '',
        ],
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    'doctrine.encryption.orm_default', // check this
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            // need this?
        ],
    ],
];