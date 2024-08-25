<?php
namespace Imccc\Snail\Services;

use Exception;
use Imccc\Snail\Core\Container;
use Imccc\Snail\Traits\IpTrait;

class LoggerService
{
    private $logQueue = []; // 日志队列，用于批量处理
    private $logFilePath; // 日志文件路径
    private $config; // 配置服务
    private $logconf; // 日志配置
    private $container; // 容器
    private $tableName;

    public function __construct(Container $container)
    {
        $this->container = $container;
        // 解析配置服务并获取日志配置信息
        $this->config = $this->container->resolve('ConfigService');
        $this->logconf = $this->config->get('logger');
        $this->tableName = $this->logconf['log_db_table'];

        // 注册一个脚本结束时的回调，用于处理日志队列中剩余的日志
        register_shutdown_function([$this, 'flushLogs']);
    }

    use IpTrait;

    /**
     * 根据配置记录日志
     *
     * @param string $message 日志消息
     * @param string $prefix 日志前缀
     */
    public function log($message, $prefix = 'def')
    {
        if ($this->logconf['on']['log']) {
            $message = $this->formatMessage($message);
            $pre = "__" . strtoupper($prefix) . "__";
            $ip = $this->ip();
            $timestamp = $this->getMicrotime();
            switch ($this->logconf['log_type']) {
                case 'file':
                    // 如果配置为使用文件记录日志且当前日志类型在配置中启用，则将日志加入队列
                    if ($this->logconf['on'][$prefix] ?? false) {
                        $this->enqueueLog("[ $ip ] $message", $pre, $timestamp);
                    }
                    break;
                case 'server':
                    // 如果配置为直接写入服务器日志，则直接写入
                    if ($this->logconf['on'][$prefix] ?? false) {
                        $this->logToServer("[$pre] [ $ip ] $message");
                    }
                    break;
                case 'database':
                    // 如果配置为记录到数据库且当前日志类型在配置中启用，则将日志加入队列
                    if ($this->logconf['on'][$prefix] ?? false) {
                        $this->enqueueLog("$message", $pre . $ip, $timestamp);
                    }
                    break;
                default:
                    // 如果配置为其他类型，则直接写入服务器日志
                    $this->logToServer("[$pre] [$ip] $message");
                    break;
            }
        }
        if (!$this->logconf['on']['report']) {
            throw new Exception('LoggerService: Logger Configure file "log" is not true.');
        }
    }

    /**
     * 格式化日志消息
     *
     * @param mixed $message 日志消息
     * @return string 格式化后的日志消息
     */
    private function formatMessage($message): string
    {
        $msgType = gettype($message);
        switch ($msgType) {
            case 'array':
            case 'object':
                return print_r($message, true);
            case 'boolean':
                return $message ? 'true' : 'false';
            default:
                return (string) $message;
        }
    }

    /**
     * 分级日志
     *
     * @param string $message 日志消息
     * @param string $prefix 日志前缀
     * @param string $type 日志类型
     */
    public function info($message, $prefix = 'info')
    {
        $this->log($message, $prefix);
    }
    public function error($message, $prefix = 'error')
    {
        $this->log($message, $prefix);
    }
    public function warn($message, $prefix = 'warn')
    {
        $this->log($message, $prefix);
    }
    public function debug($message, $prefix = 'debug')
    {
        $this->log($message, $prefix);
    }
    public function critical($message, $prefix = 'critical')
    {
        $this->log($message, $prefix);
    }
    public function alert($message, $prefix = 'alert')
    {
        $this->log($message, $prefix);
    }
    public function emergency($message, $prefix = 'emergency')
    {
        $this->log($message, $prefix);
    }
    public function notice($message, $prefix = 'notice')
    {
        $this->log($message, $prefix);
    }
    public function custom($message, $prefix = 'custom')
    {
        $this->log($message, $prefix);
    }

    /**
     * 清理日志信息
     *
     * @param string $day 保留天数
     */
    public function cleanLogs($day = 7)
    {
        switch ($this->logconf['log_type']) {
            case 'file':
                $this->cleanFileLogs($day);
                break;
            case 'database':
                $this->cleanDatabaseLogs($day);
                break;
        }
    }

    /**
     * 记录SQL调试信息 专为SQL使用 ，调试信息,只能记录到文本类型中
     * @param string $message 日志内容
     * @param string $prefix 日志前缀
     */
    public function logSql($message, $prefix = 'sql')
    {
        // 如果配置为使用文件记录日志且当前日志类型在配置中启用，则将日志加入队列
        if ($this->logconf['on']['log'] && $this->logconf['on'][$prefix]) {
            $pre = "__" . strtoupper($prefix) . "__";
            $ip = $this->ip();
            $timestamp = $this->getMicrotime();
            $this->enqueueLog("[$pre] [$ip] $message", $pre, $timestamp);
        } elseif (!$this->logconf['on']['report']) {
            throw new Exception('LoggerService: Logger Configure file "log" is not true on.');
        }
    }

    /**
     * 解析日志文件名
     *
     * @param string $type 日志类型
     * @return string 日志文件名
     */
    private function resolveFilename($type)
    {
        return $this->logconf['log_file_path'] . '/' . $type . date('YmdH') . '.log';
    }

    /**
     * 将日志消息加入队列
     */
    private function enqueueLog($message, $prefix, $time)
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        } else {
            $message = (string) $message;
        }

        $logEntry = [
            'time' => $time ?? $this->getMicrotime(),
            'message' => $message,
            'filename' => $prefix,
        ];

        $this->logQueue[] = $logEntry;

        // 如果达到批量处理的大小，则立即处理
        if (count($this->logQueue) >= $this->logconf['batch_size']) {
            $this->flushLogs();
        }
    }

    /**
     * 立即将日志队列中的日志写入到文件中或数据库中
     */
    public function flushLogs()
    {
        // 如果队列为空，则直接返回
        if (empty($this->logQueue)) {
            return;
        }

        // 按时间排序日志队列
        usort($this->logQueue, function ($a, $b) {
            return strtotime($a['time']) - strtotime($b['time']);
        });

        if ($this->logconf['log_type'] === 'database') {
            $this->flushLogsToDatabase();
        } else {
            // 对日志进行分组处理，按照文件名分组
            $logsByFile = [];
            foreach ($this->logQueue as $logEntry) {
                $filename = $this->resolveFilename($logEntry['filename']);
                $logsByFile[$filename][] = "[" . $logEntry['time'] . "] - " . $logEntry['message'];
            }

            // 分别写入对应的文件
            foreach ($logsByFile as $filename => $messages) {
                // 创建目录
                if (!is_dir($this->logconf['log_file_path'])) {
                    mkdir($this->logconf['log_file_path'], 0777, true);
                }

                file_put_contents($filename, implode(PHP_EOL, $messages) . PHP_EOL, FILE_APPEND);
            }
        }

        // 清空日志队列
        $this->logQueue = [];
    }

    /**
     * 将日志队列中的日志批量写入数据库
     */
    private function flushLogsToDatabase()
    {
        // 使用容器解析数据库服务
        $sqlService = $this->container->resolve('SqlService');
        $prefix = $sqlService->getPrefix();
        $realTableName = $prefix . $this->tableName;

        // 确保日志数据库表存在
        if (!$this->checkTableExists($realTableName)) {
            $this->createTable($realTableName);
        }

        $insertValues = [];
        $params = [];
        foreach ($this->logQueue as $index => $logEntry) {
            $type = "__" . strtoupper($logEntry['filename']) . "__";
            $insertValues[] = "(:time$index, :message$index, :type$index)";
            $params[":time$index"] = $logEntry['time'];
            $params[":message$index"] = $logEntry['message'];
            $params[":type$index"] = $type;
        }

        $sql = "INSERT INTO {$realTableName} (times, message, type) VALUES " . implode(', ', $insertValues);

        // 执行批量插入操作
        try {
            $sqlService->execute($sql, $params);
        } catch (Exception $e) {
            if ($this->logconf['on']['report']) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * 记录日志到服务器日志
     *
     * @param string $message 日志消息
     */
    private function logToServer($message)
    {
        // 添加时间戳到日志消息中
        $logMessage = $this->getMicrotime() . ' - ' . $message . PHP_EOL;
        // 记录到服务器日志
        error_log($logMessage);
    }

    /**
     * 清理旧日志文件
     *
     * @param int $daysToKeep 保留日志的天数
     */
    public function cleanFileLogs($daysToKeep = 30)
    {
        $logDirectory = $this->logconf['log_file_path'];
        if (!is_dir($logDirectory)) {
            // 日志目录不存在
            return;
        }

        $files = new \DirectoryIterator($logDirectory);
        foreach ($files as $file) {
            if ($file->isFile()) {
                $fileAgeDays = (time() - $file->getMTime()) / 86400; // 文件修改时间到现在的天数
                if ($fileAgeDays > $daysToKeep) {
                    unlink($file->getPathname()); // 删除文件
                }
            }
        }
    }

    /**
     * 清理旧的数据库日志
     *
     * @param int $daysToKeep 保留日志的天数
     */
    public function cleanDatabaseLogs($daysToKeep = 7)
    {
        $sqlService = $this->container->resolve('SqlService');
        $prefix = $sqlService->getPrefix();
        $realTableName = $prefix . $this->tableName;

        $sql = "DELETE FROM {$realTableName} WHERE DATEDIFF(NOW(), times) > :daysToKeep";

        try {
            $sqlService->execute($sql, [':daysToKeep' => $daysToKeep]);
        } catch (Exception $e) {
            // 记录到服务器日志
            if ($this->logconf['on']['report']) {
                throw new Exception("Failed to clean up database: " . $e->getMessage());
            }
        }
    }

    /**
     * 检查日志表是否存在
     *
     * @return bool 返回 true 如果表存在，否则返回 false
     */
    private function checkTableExists($table)
    {
        $sqlService = $this->container->resolve('SqlService'); // 解析 SqlService 对象
        $checkTableExistsSql = "SHOW TABLES LIKE '$table'"; // link SQL一定要是单引号
        $tableExists = $sqlService->query($checkTableExistsSql);
        return !empty($tableExists);
    }

    /**
     * 创建日志表
     */
    private function createTable($table)
    {
        $sqlService = $this->container->resolve('SqlService'); // 解析 SqlService 对象
        $columns = [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'times' => 'DATETIME(6)', // 支持微秒精度的时间戳
            'message' => 'TEXT',
            'type' => 'VARCHAR(255)',
        ];
        try {
            $sqlService->createTable($table, $columns);
        } catch (Exception $e) {
            if ($this->logconf['on']['report']) {
                throw new Exception("Failed to create log table: " . $e->getMessage());
            }
        }
    }

    /**
     * 获取当前时间的微秒精度时间戳
     * @return string
     */
    private function getMicrotime()
    {
        $microtime = microtime(true);
        $micro = sprintf("%06d", ($microtime - floor($microtime)) * 1000000);
        $date = new \DateTime(date('Y-m-d H:i:s.' . $micro, $microtime));
        return $date->format("Y-m-d H:i:s.u");
    }
}
