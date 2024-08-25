<?php

return [
    'server' => [
        'type' => 'socket', // 服务器类型，可选值：socket, http
        'host' => '127.0.0.1',
        'port' => 8181,
        'auth' => ['admin:admin', 'root:root'],
    ],
    'client' => [
        'type' => 'socket', // 客户端类型，可选值：socket, http
        'host' => '127.0.0.1',
        'port' => 8182,
        'auth' => ['admin:admin', 'root:root'],
    ],
];
