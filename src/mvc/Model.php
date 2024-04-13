<?php

namespace Imccc\Snail\Mvc;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Mvc\IModel;
use PDOException;

class Model implements IModel
{
    protected $container;
    protected $sqlService;
    protected $logger;
    protected $table;
    protected $conditions = [];
    protected $fields = ['*'];
    protected $prefix;
    protected $softDeletes = true; // 假设默认开启软删除

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->sqlService = $container->resolve('SqlService');
        $this->logger = $container->resolve('LoggerService');
        $this->prefix = $this->sqlService->getPrefix();
        $this->softDeleteField = $this->sqlService->getSoftDeleteField();
    }

    /**
     * 设置软删除
     * @param bool $enabled
     * @return $this
     */
    public function withSoftDeletes(bool $enabled = true): self
    {
        $this->softDeletes = $enabled;
        return $this;
    }

    /**
     * 设置模型表名
     * @param string $table
     * @return $this
     */
    public function setModel(string $table): self
    {
        $this->table = $this->prefix . $table;
        return $this;
    }

    /**
     * 设置查询条件
     * @param array $conditions
     * @return $this
     */
    public function where(array $conditions): self
    {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * 设置查询字段
     * @param array $fields
     * @return $this
     */
    public function select(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * 查询数据
     * @return array
     */
    public function find(): array
    {
        // 如果启用了软删除，自动添加条件
        if ($this->softDeletes) {
            $this->conditions[$this->softDeleteField] = null;
        }
        try {
            $result = $this->sqlService->select($this->table, $this->fields, $this->conditions);
            $this->reset();
            return $result ?: [];
        } catch (PDOException $e) {
            // 处理异常或记录日志
            $this->handleException($e);
            return [];
        }
    }

    /**
     * 插入数据
     * @param array $data
     * @return bool
     */
    public function insert(array $data): bool
    {
        $this->beforeSave();
        try {
            $result = $this->sqlService->insert($this->table, $data);
            $this->afterSave();
            return $result;
        } catch (PDOException $e) {
            $this->handleException($e);
            return false;
        }
    }

    /**
     * 更新数据
     * @param array $data
     * @return bool
     */
    public function update(array $data): bool
    {
        $this->beforeSave();
        try {
            $result = $this->sqlService->update($this->table, $data, $this->conditions);
            $this->afterSave();
            return $result;
        } catch (PDOException $e) {
            $this->handleException($e);
            return false;
        }
    }

    /**
     * 删除数据
     * @return bool
     */
    public function delete(): bool
    {
        if ($this->softDeletes) {
            // 实现软删除
            return $this->update([$this->softDeleteField => date('Y-m-d H:i:s')]);
        }

        try {
            $result = $this->sqlService->delete($this->table, $this->conditions);
            $this->reset();
            return $result;
        } catch (PDOException $e) {
            $this->handleException($e);
            return false;
        }
    }

    protected function beforeSave(): void
    {
        // 自定义逻辑，比如清理、验证等
    }

    protected function afterSave(): void
    {
        // 自定义逻辑，比如清理缓存、发送通知等
    }

    protected function handleException(PDOException $e): void
    {
        // 这里可以添加异常处理逻辑，比如记录日志等
        $this->logger->log('SQL Error: ' . $e->getMessage());
        // throw $e; // 或者重新抛出异常
        throw $e;
    }

    protected function reset(): void
    {
        $this->conditions = [];
        $this->fields = ['*'];
    }
}
