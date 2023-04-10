<?php
return [
    'default' => 'pgsql',
    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
        'pgsql2' => [
            'driver' => 'pgsql',
            'host' => env('DB2_HOST'),
            'database' => env('DB2_DATABASE'),
            'username' => env('DB2_USERNAME'),
            'password' => env('DB2_PASSWORD'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
        'pgsql_test' => [
            'driver' => 'pgsql',
            'host' => env('DB3_HOST'),
            'database' => env('DB3_DATABASE'),
            'username' => env('DB3_USERNAME'),
            'password' => env('DB3_PASSWORD'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],

        // 'redis' => [

        //     'client' => 'predis',

        //     'default' => [
        //         'host' => env('REDIS_HOST', '127.0.0.1'),
        //         'password' => env('REDIS_PASSWORD', ''), //if password otherwise set null
        //         'port' => env('REDIS_PORT', 6379),
        //         'database' => 0,
        //     ],

        // ],

        'redis' => [

            'client' => env('REDIS_CLIENT', 'predis'),

            'default' => [
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_PORT', 6379),
                'database' => env('REDIS_DB', 0),
            ],

            'cache' => [
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_PORT', 6379),
                'database' => env('REDIS_CACHE_DB', 1),
            ],

        ],


    ]
];
