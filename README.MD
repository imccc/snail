# Snail PHP 

Snail PHP 是一个由AI训练出来的框架，旨在帮助开发者快速开发项目。

## 特性

- 快速开发
- 轻量级
- 模块化
- 容器化
- 框架内核
- 框架扩展
- 框架插件
- 框架工具
- 框架配置
- 框架路由
- 框架日志
- 框架缓存
- 框架会话
- 框架验证
- 框架数据库
- 框架缓存

## 安装

```bash
composer require imccc/snail
```

## 快速开始

```php
<?php
namespace Snail\App;
use Imccc\Snail\Snail;

define("START_TIME", microtime(true)); //程序开始时间 ,设置了直接显示，不设置了，可以不显示
define("DEBUG", true); //是否开启调试模式
define("APP_PATH", dirname(__DIR__) . '/app'); //应用目录
define('APP_CONFIG_PATH', APP_PATH . '/config'); //配置文件目录
define('CFG_EXT', '.conf.php'); //配置文件后缀
define('CS', '.'); //配置文件分隔符

require_once dirname(__DIR__) . '/vendor/autoload.php';

new Snail();
```