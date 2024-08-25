<?php

return [
    'on' => [
        // 总开关
        'isdebug' => true, //调试总开关，如果为false则关闭所有
        'log' => true, // 日志总开关，如果为false则关闭所有
        'report' => true, // 错误报告总开关，如果为false则关闭所有

        // 分级日志
        'def' => true,
        'info' => true,
        'warning' => true,
        'error' => true,
        'notice' => true,
        'alert' => true,
        'critical' => true,
        'emergency' => true,
        'custom' => true,
        // 模块日志
        'welcome' =>true,
        'debugLog' =>true,
        'http' => true,
        'router' => true,
        'dispatcher' => true,
        'middleware' => true,
        'controller' => true,
        'view' => true,
        'model' => true,
        'database' => true,
        'config' =>true,
        // 服务日志
        'sql' => true,
        'sqlerr' => true,
        'config' => true,
        'container' => true,
        'service' => true,
        'socket' => true,
        'request' => true,
        'response' => true,
        'engine' => true,
        'template'=>true,
        // 其它自定义调试信息开关，可自行增加
    ],
    'log_file_path' => dirname($_SERVER['DOCUMENT_ROOT']) . '/runtime/logs', // 日志文件路径
    'batch_size' => 100, // 批量处理的大小,仅对file类型有效
    'log_type' => 'file', // 日志类型，可选值：file, server, database
    'log_db_table' => 'logs', // 数据库表名 ,不考虑前缀，和数据库配置通用。
];
