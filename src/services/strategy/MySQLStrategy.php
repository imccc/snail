<?php
namespace Imccc\Snail\Services\Strategy;

use Imccc\Snail\Interfaces\DatabaseStrategyInterface;
use Exception;
use PDO;
use PDOException;

class MySQLStrategy implements DatabaseStrategyInterface
{
    private $pdo;
    private $config;

    public function __construct($pdo, $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    public function backupDatabase($backupFilePath)
    {
        try {
            // 获取当前数据库名称
            $dbName = $this->config['dsn'][$this->config['db']]['dbname'];

            // 生成备份命令
            $backupCommand = "mysqldump --user={$this->config['dsn'][$this->config['db']]['user']} --password={$this->config['dsn'][$this->config['db']]['password']} --host={$this->config['dsn'][$this->config['db']]['host']} $dbName > $backupFilePath";

            // 执行备份命令
            exec($backupCommand, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new Exception("备份失败，错误代码 $returnVar");
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("数据库备份失败！错误信息: " . $e->getMessage());
        }
    }

    public function restoreDatabase($backupFilePath)
    {
        try {
            // 获取当前数据库名称
            $dbName = $this->config['dsn'][$this->config['db']]['dbname'];

            // 生成恢复命令
            $restoreCommand = "mysql --user={$this->config['dsn'][$this->config['db']]['user']} --password={$this->config['dsn'][$this->config['db']]['password']} --host={$this->config['dsn'][$this->config['db']]['host']} $dbName < $backupFilePath";

            // 执行恢复命令
            exec($restoreCommand, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new Exception("恢复失败，错误代码 $returnVar");
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("数据库恢复失败！错误信息: " . $e->getMessage());
        }
    }

    public function createView($viewName, $selectStatement)
    {
        $sql = "CREATE VIEW $viewName AS $selectStatement";
        try {
            $this->pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            throw new Exception("创建视图失败: " . $e->getMessage());
        }
    }

    public function dropView($viewName)
    {
        $sql = "DROP VIEW IF EXISTS $viewName";
        try {
            $this->pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            throw new Exception("删除视图失败: " . $e->getMessage());
        }
    }

    public function createTrigger($triggerName, $timing, $event, $table, $statement)
    {
        $sql = "CREATE TRIGGER $triggerName $timing $event ON $table FOR EACH ROW $statement";
        try {
            $this->pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            throw new Exception("创建触发器失败: " . $e->getMessage());
        }
    }

    public function dropTrigger($triggerName)
    {
        $sql = "DROP TRIGGER IF EXISTS $triggerName";
        try {
            $this->pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            throw new Exception("删除触发器失败: " . $e->getMessage());
        }
    }

    public function createProcedure($procedureName, $procedureDefinition)
    {
        $sql = "CREATE PROCEDURE $procedureName $procedureDefinition";
        try {
            $this->pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            throw new Exception("创建存储过程失败: " . $e->getMessage());
        }
    }

    public function dropProcedure($procedureName)
    {
        $sql = "DROP PROCEDURE IF EXISTS $procedureName";
        try {
            $this->pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            throw new Exception("删除存储过程失败: " . $e->getMessage());
        }
    }
}
