<?php
// 路由配置
//情形一： 'key' => ['方法@控制器', '请求方式/默认为get，可以为空','可选指定命名空间‘],
//情况二： 'key' => [ '闭包函数', '请求方式/默认为get，可以为空'],
//情况三： 不包含相关信息，直接解析构建

//其它： routemap中的分组，namespace对应的是 namespacemap中的值

return [
    'keyvalue' => true, //使用键对参数 user/abc/id/2,不使用键对参数 user/1/edit
    'def' => [
        'default_namespace' => 'app\index\controller',
        'default_controller' => 'Index',
        'default_action' => 'index',
    ],
    'namespacemap' => [
        'web' => 'app\index\controller',
        'admin' => 'app\admin\controller',
        'api' => 'app\api\controller',
        'home' => 'app\home\controller',
        'blog' => 'app\blog\controller',
    ],
    'routemap' => [
        'web' => [
            // http://domain.com/user/1/edit
            'user/:id/:action' => ['user@Index', 'GET|POST'],

            // http://domain.com/hello/abc
            'hello/:any' => ['hello@Index', 'get','app\index\controller'],

            // http://domain.com
            '/' => ['index@Index', 'get'], // 首页路由

            // http://domain.com/sendmail
            'sendmail' => [ 'sendmail@Index', 'post|get'],

            // http://domain.com/about
            'admin' => [ 'about@Index'],

            // http://domain.com/welcome 直接输出
            'welcome' => [function () {
                echo "Welcome to use Snail Framework";
            }, 'get|post'],

            // http://domain.com/submit
            'submit' => [ 'submit@Blog', 'post|get'],
        ],
        'admin' => [
            'admin/user/:id' => ['user@Index', 'GET|POST'],
            'admin/user/:id/:action' => [ 'user@Index', 'GET|POST'],
        ],
        'api' => [
            'api/user/:id' => ['user@Index', 'GET|POST'],
            'api/user/:id/:action' => ['user@Index', 'GET|POST'],
        ],
    ],
];
