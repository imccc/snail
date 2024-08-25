这个类是一个日志服务类，旨在提供统一的日志记录功能。以下是该类的详细说明：

### 构造函数 `__construct(Container $container)`
- 接受一个 `Container` 对象作为参数，用于依赖注入。
- 解析配置服务并获取日志配置信息。
- 注册一个脚本结束时的回调，用于处理日志队列中剩余的日志。

### 公共方法 `log(string $message, string $prefix = 'def')`
- 根据配置记录日志。
- `$message` 参数为日志消息。
- `$prefix` 参数为日志前缀，默认为 `'def'`。
- 根据配置的日志类型和前缀，将日志消息记录到文件、服务器或数据库中。

### 公共方法 `cleanLogs(int $day = 7)`
- 清理日志信息。
- 根据配置的日志类型，清理文件或数据库中的日志。
- `$day` 参数指定保留日志的天数，默认为 `7` 天。

### 公共方法 `logSql(string $message, string $prefix = 'sql')`
- 记录 SQL 调试信息，专为 SQL Server 使用。
- `$message` 参数为日志内容。
- `$prefix` 参数为日志前缀，默认为 `'sql'`。
- 将日志消息记录到文件、服务器或数据库中。

### 私有方法 `resolveFilename(string $type)`
- 解析日志文件名。
- 根据日志类型和当前时间生成相应的日志文件名。

### 私有方法 `enqueueLog(string $message, string $prefix)`
- 将日志消息加入队列。
- 将日志消息及相关信息加入日志队列，用于批量处理。

### 公共方法 `flushLogs()`
- 立即将日志队列中的日志写入到文件中。
- 对日志进行分组处理，按照文件名分组，并写入对应的文件。
- 清空日志队列。

### 私有方法 `logToServer(string $message)`
- 记录日志到服务器日志。
- 将日志消息记录到服务器日志中。

### 私有方法 `logToDatabase(string $message, string $type = 'def')`
- 记录日志到数据库。
- 将日志消息记录到数据库中。
- `$type` 参数为日志类型，默认为 `'def'`。

### 公共方法 `cleanFileLogs(int $daysToKeep = 30)`
- 清理旧日志文件。
- 删除指定天数前的日志文件。
- `$daysToKeep` 参数指定保留日志的天数，默认为 `30` 天。

### 公共方法 `cleanDatabaseLogs(int $daysToKeep = 7)`
- 清理旧的数据库日志。
- 删除指定天数前的数据库日志记录。
- `$daysToKeep` 参数指定保留日志的天数，默认为 `7` 天。

### 私有方法 `checkTableExists(string $table)`
- 检查日志表是否存在。
- 查询数据库，判断日志表是否存在。
- `$table` 参数为数据库表名。

### 私有方法 `createTable(string $table)`
- 创建日志表。
- 在数据库中创建日志表。
- `$table` 参数为数据库表名。

这个类提供了灵活的日志记录功能，可以根据配置将日志记录到文件、服务器或数据库中，同时也提供了清理日志的功能。