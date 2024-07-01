<?php

return [
    'driver' => 'file',
    'expiration' => 3600, // 缓存过期时间，单位秒'
    'driverConfig' => [
        'file' => [
            'path' => dirname($_SERVER['DOCUMENT_ROOT']) . '/runtime/cache', // 日志文件路径,
        ],
        'redis' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'auth' => '',
            'password' => '',
            'db' => 0,
        ],
        'memcached' => [
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 1,
            'timeout' => 1000,
            'retry_interval' => 15,
            'status' => true,
            'failure_callback' => function ($host, $port) {
                echo "Memcached server $host:$port failed\n";
            },
            'success_callback' => function ($host, $port) {
            },
        ],
        'mongodb' => [
            'host' => '127.0.0.1',
            'port' => 27017,
            'db' => 'test',
            'collection' => 'cache',
            'username' => '',
            'password' => '',
            'options' => [],
        ],
    ],
];
