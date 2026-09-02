<?php

return [

    'paths'                    => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods'          => ['*'],

    // 👇 Replace or add your actual frontend domain here
    'allowed_origins'          => [
        'https://jared-mitchell.vercel.app',
        'https://jared-mitchell-three.vercel.app',
        'http://localhost:3000',
        'https://oursocialimage.net'
    ],



    'allowed_origins_patterns' => [],

    'allowed_headers'          => ['*'],

    'exposed_headers'          => [],

    'max_age'                  => 0,

    'supports_credentials'     => true,
];
