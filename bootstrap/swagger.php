<?php

/**
 * Swagger UI configuration
 */
return [
    'routes' => [
        [
            'path' => '/api/documentation',
            'middleware' => [],
            'swagger' => [
                'yaml' => base_path('swagger.yaml'),
            ],
        ],
    ],
];
