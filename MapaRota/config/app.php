<?php
/**
 * Application Configuration
 */

return [
    'name'     => 'CampusTrack',
    'version'  => '2.0.0',
    'debug'    => true,
    'base_url' => '/campustrack',
    'timezone' => 'America/Sao_Paulo',

    'cors' => [
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization'],
    ],

    'map' => [
        'default_image' => 'mapa.png',
        'width'         => 1600,
        'height'        => 900,
    ]
];
