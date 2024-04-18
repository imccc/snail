<?php
/**
 * 日志服务
 *
 * @package Imccc\Snail\Services
 * @author  Imccc
 * @version 0.0.1
 * @status  beta
 * @copyright Copyright (c) 2024 Imccc.
 * @license Apache-2.0
 * @last_modified_at 2024-04-12 23:08
 *
 * @warning logger.conf.php 配置文件中，去掉了写入类型的前缀，改为自动前缀，
 * 但是在on中，如果不存在。则不会写入。需要要手动增加在on数组中，当前已修得sql表不存在的问题
 */

namespace Imccc\Snail\Services;

use Exception;
use Imccc\Snail\Core\Container;
use Imccc\Snail\Traits\DebugTrait;
use Imccc\Snail\Traits\HandleExceptionTrait;

class LoggerService
{
    private $logQueue = []; // 日志队列，用于批量处理
    private $logFilePath; // 日志文件路径
    private $config; // 配置服务
    private $logconf; // 日志配置
    private $container; // 容器
    private $tableName;

    use HandleExceptionTrait, DebugTrait;

    public function __construct(Container $container)
    {
        // 注册全局异常处理函数
        set_error_handler([self::class, 'handleException']);

        $this->container = $container;
        // 解析配置服务并获取日志配置信息
        $this->config = $this->container->resolve('ConfigService');
        $this->logconf = $this->config->get('logger');
        $this->tableName = $this->logconf['log_db_table'];
        // 注册一个脚本结束时的回调，用于处理日志队列中剩余的日志
        register_shutdown_function([$this, 'flushLogs']);
        register_shutdown_function([self::class, 'debug']);
    }

    /**
     * 根据配置记录日志
     *
     * @param string $message 日志消息
     * @param string $prefix 日志前缀
     */
    public function log($message, $prefix = 'def')
    {
        $pre = "__" . strtoupper($prefix) . "__";
        switch ($this->logconf['log_type']) {
            case 'file':
                // 如果配置为使用文件记录日志且当前日志类型在配置中启用，则将日志加入队列
                if ($this->logconf['on'][$prefix] ?? false) {
                    $this->enqueueLog("[$pre] $message", $pre);
                }
                break;
            case 'server':
                // 如果配置为直接写入服务器日志，则直接写入
                if ($this->logconf['on'][$prefix] ?? false) {
                    $this->logToServer("[$pre] $message");
                }
                break;
            case 'database':
                // 如果配置为记录到数据库且当前日志类型在配置中启用，则记录到数据库
                if ($this->logconf['on'][$prefix] ?? false) {
                    $this->logToDatabase("$message", $pre);
                }
                break;
            default:
                // 如果配置为其他类型，则直接写入服务器日志
                $this->logToServer("[$pre] $message");
                self::handleException('Invalid log type, to see server logs');
                break;
        }
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
        $pre = "__" . strtoupper($prefix) . "__";
        // 如果配置为使用文件记录日志且当前日志类型在配置中启用，则将日志加入队列
        if ($this->logconf['on'][$prefix] ?? false) {
            $this->enqueueLog("[$pre] $message", $pre);
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
    private function enqueueLog($message, $prefix)
    {
        $logEntry = [
            'time' => date('Y-m-d H:i:s'),
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
     * 立即将日志队列中的日志写入到文件中
     */
    public function flushLogs()
    {
        // 如果队列为空，则直接返回
        if (empty($this->logQueue)) {
            return;
        }

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

        // 清空日志队列
        $this->logQueue = [];
    }

    /**
     * 记录日志到服务器日志
     *
     * @param string $message 日志消息
     */
    private function logToServer($message)
    {
        // 添加时间戳到日志消息中
        $logMessage = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;
        // 记录到服务器日志
        error_log($logMessage);
    }

    /**
     * 记录日志到数据库
     *
     * @param string $message 日志消息
     * @param string $type 日志类型
     * @param string $tableName 数据库表名
     */
    private function logToDatabase($message, $type = 'def')
    {
        // 使用容器解析数据库服务
        $sqlService = $this->container->resolve('SqlService');
        $prefix = $sqlService->getPrefix();
        $realTableName = $prefix . $this->tableName;

        // 确保日志数据库表存在
        if (!$this->checkTableExists($realTableName)) {
            $this->createTable($realTableName);
        }

        $type = "__" . strtoupper($type) . "__";

        // 准备插入语句
        $sql = "INSERT INTO {$realTableName} (times, message, type) VALUES (:times, :message, :type)";

        // 准备参数数组
        $params = [
            ':times' => date('Y-m-d H:i:s'),
            ':message' => $message,
            ':type' => $type,
        ];

        // 绑定参数并执行插入操作
        try {
            $sqlService->execute($sql, $params);
        } catch (Exception $e) {
            self::handleException("Failed to log to database: " . $e->getMessage());
        }
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
            self::handleException("Failed to clean up database logs: " . $e->getMessage());
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
            'times' => 'DATETIME',
            'message' => 'TEXT',
            'type' => 'VARCHAR(255)',
        ];
        try {
            $sqlService->createTable($table, $columns);
        } catch (Exception $e) {
            // 记录到服务器日志
            self::handleException("Failed to create log table: " . $e->getMessage());
        }
    }

}
