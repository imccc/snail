<?php

return [

    'host' => 'mail.imccc.cc',
    'port' => 25,
    'tls' => false,
    'ssl' => false,
    'auth' => true,
    'username' => 'sam@imccc.cc',
    'password' => 'Bcc@1205',
    'connectionTimeout' => 30,
    'responseTimeout' => 30,
    'debug' => true,
    'log' => true,
    'log_path' => dirname($_SERVER['DOCUMENT_ROOT']) . '/runtime/mail', // 日志文件路径,
    'log_level' => 'debug',
    'log_format' => '[%datetime%] %channel%.%level_name%: %message% %context% %extra%',
    'log_date_format' => 'Y-m-d H:i:s',
    'log_timezone' => 'Asia/Shanghai',
];
