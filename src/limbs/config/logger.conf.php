<?php

return [
    'debug' => true,
    'on' => [
        'def' => true,
        'log' => true,
        'info' => true,
        'error' => true,
        'warning' => true,
        'debug' => true,
        'dispatch' => true,
        'route' => true,
        'middleware' => true,
        'config' => true,
        'sql' => true,
        'sqlerr' => true,
        'view' => true,
        'model' => true,
        'controller' => true,
        'container' => true,
        'socket' => true,
        'request' => true,
        'response' => true,
        'database' => true,
        'http' => true,
    ],
    'log_file_path' => dirname($_SERVER['DOCUMENT_ROOT']) . '/runtime/logs', // 日志文件路径
    'log_type' => 'file', // 日志类型，可选值：file, server, database
    'batch_size' => 100, // 批量处理的大小,仅对file类型有效
];
