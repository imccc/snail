# Snail 系统说明书
Snail 框架基于 PHP7.0+，支持 Windows、Linux、Mac 等多种操作系统。

## 快速开始
1、使用 composer 安装：composer require imccc/snail
2、使用 composer 创建项目：composer create-project imccc/snail
3、git clone 项目：git clone https://github.com/imcccphp/snail.git

## 版权
Copyright (c) 2018-2024 imccc.cc
License: Apache License Version 2.0, January 2004
http://www.apache.org/licenses/

## 框架目录结构
```
Snail
|- app
|  |- config
|  |  |- cache.conf.php
|  |  |- database.conf.php
|  |  |- logger.conf.php
|  |  |- def.conf.php
|  |  |- route.conf.php
|  |  |- snail.conf.php
|  |  |- socket.conf.php
|  |- controller
|  |  |- IndexController.php
|  |- model
|  |  |- IndexModel.php
|  |- service
|  |  |- IndexService.php
|  |- view
|  |  |- index.html
|- public
|  |- index.php
|- runtime
|  |- cache
|  |- log
|- vendor
|  |- imccc
|  |  |- snail
|  |  |  |- core
|  |  |  |  |- Container.php
|  |  |  |  |- Dispatcher.php
|  |  |  |  |- HandlerException.php
|  |  |  |  |- MiddlewareInterface.php
|  |  |  |  |- Router.php
|  |  |  |- helper
|  |  |  |  |- CurlHelper.php
|  |  |  |  |- EncryptionHelper.php
|  |  |  |  |- IpHelper.php
|  |  |  |  |- StringHelper.php
|  |  |  |  |- TimeHelper.php
|  |  |  |- limbs
|  |  |  |  |- config
|  |  |  |  |  |- Cache.php
|  |  |  |  |- doc
|  |  |  |  |  |- Cache.php
|  |  |  |  |- i18n
|  |  |  |  |- test
|  |  |  |  |  |- test.http
|  |  |  |- mvc
|  |  |  |  |- Controller.php
|  |  |  |  |- Model.php
|  |  |  |  |- View.php
|  |  |  |- services
|  |  |  |  |- drivers
|  |  |  |  |  |- FileCacheDriver.php
|  |  |  |  |  |- MemcachedDriver.php
|  |  |  |  |  |- MongoCacheDriver.php
|  |  |  |  |  |- RedisCacheDriver.php
|  |  |  |  |- CacheService.php
|  |  |  |  |- LoggerService.php
|  |  |  |  |- ConfigService.php
|  |  |  |  |- FileService.php

|  |  |  |- Snail.php
```

## SqlService

```
这是一个用于数据库操作的PHP类，提供了连接数据库、执行查询、插入、更新、删除等功能，并支持事务、分页查询、批量操作、表格操作以及导入导出SQL等功能。

该类的主要方法包括：

- `__construct`: 构造函数，用于连接数据库。
- `query`: 执行查询并返回所有结果。
- `insert`: 插入单条数据。
- `update`: 更新单条数据。
- `delete`: 删除单条数据。
- `execute`: 执行SQL语句。
- `fetch`: 执行查询并返回第一行结果。
- `fetchAll`: 执行查询并返回所有结果。
- `lastInsertId`: 获取最后插入行的ID。
- `innerJoin`: 生成内连接SQL。
- `leftJoin`: 生成左连接SQL。
- `rightJoin`: 生成右连接SQL。
- `on`: 指定连接条件。
- `complexCondition`: 生成复杂条件SQL。
- `beginTransaction`: 开始事务。
- `commit`: 提交事务。
- `rollback`: 回滚事务。
- `bindParams`: 参数绑定。
- `paginate`: 分页查询。
- `batchInsert`: 批量插入数据。
- `batchUpdate`: 批量更新数据。
- `batchDelete`: 批量删除数据。
- `createTable`: 建表。
- `truncateTable`: 清空表。
- `dropTable`: 删除表。
- `alterTable`: 更新表格。
- `exportSql`: 导出SQL。
- `importSql`: 导入SQL或ZIP文件。

该类还包括一些辅助方法和私有方法，用于处理SQL文件的导入导出和目录的删除操作。
```
## LoggerService
LoggerService 是一个用于日志记录的 PHP 类，提供了日志记录功能，支持日志级别、日志格式、日志文件大小限制、日志文件数量限制、日志文件名格式等配置选项。
LoggerService 类的主要方法包括：
```
1. 构造函数 `__construct`：初始化日志服务并注册一个脚本结束时的回调函数，用于处理日志队列中剩余的日志。
2. 方法 `log`：根据配置文件记录日志，可以将日志写入文件、服务器或数据库。
3. 方法 `resolveFilename`：解析日志文件名，根据日志类型确定文件名。
4. 方法 `enqueueLog`：将日志消息加入队列，如果队列达到批量处理的大小，则立即处理。
5. 方法 `flushLogs`：将日志队列中的日志写入文件。
6. 方法 `logToServer`：将日志记录到服务器日志。
7. 方法 `logToDatabase`：将日志记录到数据库。
```