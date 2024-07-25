<?php

return [
    'keyValue' => false, // 是否使用键值对形式的参数
    'setup' => [
        'guiseExtend' => 'html|do|snail|act|py|c|java|go', //文件后缀伪装
        'application' => 'App', // 应用目录名称
        'controller' => 'Controller', // 控制器目录名称
        'defaultNamespace' => 'App\Index\Controller', // 默认命名空间
        'defaultGroup' => 'index', // 默认路由组
    ],
    'routeMap' => [
        // 验证码
        [
            'rule' => '/captcha/{timestamp}', // 路由规则 使用示例：/captcha/0.1234567890
            'route' => 'Captcha@index', // 控制器@方法
            'method' => 'GET', // 请求方法
        ],
        // 普通路由定义
        [
            'rule' => '/user/{id}', // 路由规则
            'route' => 'App\Index\Controller\UserController@show', // 控制器@方法
            'method' => 'GET', // 请求方法
        ],
        // 闭包路由示例
        [
            'rule' => '/hello/{name}', // 路由规则
            'route' => function ($name) { // 闭包函数
                return "Hello, $name!";
            },
            'method' => 'GET', // 请求方法
        ],
        [
            'rule' => '/', // 路由规则
            'route' => 'App\Index\Controller\IndexController@index', // 控制器@方法
            'method' => 'GET', // 请求方法
        ],
        [
            'rule' => '/about', // 路由规则
            'route' => 'App\Index\Controller\IndexController@about', // 控制器@方法
            'method' => 'GET', // 请求方法
        ],
        // 分组路由示例 1: Admin 分组
        [
            'group' => [
                'name' => 'Admin',
                'prefix' => '/admin',
                'namespace' => 'App\Admin\Controller',
                'middleware' => [
                    // 可以在这里添加组的中间件
                ],
                'routes' => [
                    [
                        'rule' => '/',
                        'route' => 'App\Admin\Controller\DashboardController@index',
                        'method' => 'GET',
                    ],
                    [
                        'rule' => '/settings',
                        'route' => 'App\Admin\Controller\SettingsController@index',
                        'method' => 'GET',
                    ],
                ],
            ],
        ],
        // 分组路由示例 2: API 分组
        [
            'group' => [
                'name' => 'API',
                'prefix' => '/api',
                'namespace' => 'App\Api\Controller',
                'middleware' => [
                    // 可以在这里添加组的中间件
                ],
                'routes' => [
                    [
                        'rule' => '/users',
                        'route' => 'App\API\Controller\UserController@index',
                        'method' => 'GET',
                    ],
                    [
                        'rule' => '/users/{id}',
                        'route' => 'App\API\Controller\UserController@show',
                        'method' => 'GET',
                    ],
                ],
            ],
        ],
    ],
];
