<?php

return [
    'on' => [
        'debug' => false, //总开关，如果为false则关闭所有

        'def' => true,
        // 分级日志
        'log' => true,
        'info' => true,
        'warning' => true,
        'error' => true,
        // 模块日志
        'http' => true,
        'route' => true,
        'dispatch' => true,
        'middleware' => true,
        'controller' => true,
        'view' => true,
        'model' => true,
        'database' => true,
        // 服务日志
        'sql' => true,
        'sqlerr' => true,
        'config' => true,
        'container' => true,
        'service' => true,
        'socket' => true,
        'request' => true,
        'response' => true,
    ],
    'log_file_path' => dirname($_SERVER['DOCUMENT_ROOT']) . '/runtime/logs', // 日志文件路径
    'log_type' => 'file', // 日志类型，可选值：file, server, database
    'batch_size' => 100, // 批量处理的大小,仅对file类型有效
];
