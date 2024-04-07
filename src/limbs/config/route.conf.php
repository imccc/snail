<?php
// 路由配置
// 'key' => ['请求地址或者闭包方式', '命名空间', '方法@类名', '模式'],
return [
    'web' => [
        'user/:id/:action' => ['user/:id/:action', 'app\index\controller', 'user@Index', 'GET|POST'],

        // http://domain.com/hello/sam
        'hello/:any' => ['helle/:any', 'app\index\controller', 'hello@Index', 'get'],

        // http://domain.com
        '/' => ['index', 'app\index\controller', 'index@Index', 'get'], // 首页路由

        'sendmail' => ['sendmail', 'app\index\controller', 'sendmail@Index', 'post|get'],

        'test' => ['test', 'app\index\controller', 'test@Index', 'get'],

        // http://domain.com/about
        'about' => ['about', 'app\index\controller', 'about@Index'],
        'snail' => ['snail', 'app\index\controller', 'snail@Index'],

        // http://domain.com/welcome 直接输出
        'welcome' => [function () {
            echo "Welcome to use Snail Framework";
        }, 'get|post'],

        // http://domain.com/submit
        'submit' => ['subbmit', 'app\index\controller', 'submit@Blog', 'post|get'],
    ],
    'admin' => [
        'admin/user/:id' => ['admin/user/:id', 'app\admin\controller', 'user@Index', 'GET|POST'],
        'admin/user/:id/:action' => ['admin/user/:id/:action', 'app\admin\controller', 'user@Index', 'GET|POST'],
    ],
    'api' => [
        'api/user/:id' => ['api/user/:id', 'app\api\controller', 'user@Index', 'GET|POST'],
        'api/user/:id/:action' => ['api/user/:id/:action', 'app\api\controller', 'user@Index', 'GET|POST'],
    ],
];
