<?php

return [
    'db' => 'mysql',
    'longconnect' => true, // 是否启用长连接
    'deleted_at' => 'deleted_at', // 软删除字段名称
    'soft_deletes' => true, // 是否启用软删除
    'dsn' => [
        'mysql' => [
            'host' => '127.0.0.1',
            'dbname' => 'snail_local',
            'user' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'port' => '3306',
            'prefix' => 'snail_',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //
                PDO::ATTR_EMULATE_PREPARES => true,
                PDO::ATTR_TIMEOUT => 30, // 设置超时时间为 30 秒
            ],
        ],
    ],
    [
        'sqlsrv' => [
            'host' => 'localhost',
            'dbname' => 'test',
            'user' => 'root',
            'password' => 'admin',
            'charset' => 'utf8',
            'port' => '1443',
            'prefix' => 'snail_',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
    ],
    [
        'oci' => [
            'host' => 'localhost',
            'dbname' => 'test',
            'user' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'port' => '3306',
            'prefix' => 'snail_',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
    ],
];
