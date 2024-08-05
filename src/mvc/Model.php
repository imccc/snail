<?php

namespace Imccc\Snail\Mvc;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Interfaces\ModelInterface;
use PDOException;

/**
 * 数据模型类，用于数据库操作。
 * 实现了ModelInterface接口，提供了CRUD（创建、读取、更新、删除）的基本方法。
 */
class Model implements ModelInterface
{
    // 依赖注入容器，用于解析依赖关系。
    protected $container;
    // SQL服务实例，用于执行数据库操作。
    protected $sqlService;
    // 日志服务实例，用于记录日志。
    protected $logger;
    // 日志前缀，用于分类日志。
    protected $logprefix = ['model', 'error', 'debug'];
    // 数据表名称。
    protected $table;
    // 查询条件。
    protected $conditions = [];
    // 查询字段。
    protected $fields = ['*'];
    // 数据表前缀。
    protected $prefix;
    // 是否启用软删除。
    protected $softDeletes = true;
    // join操作的信息。
    protected $joins = [];
    // 排序信息。
    protected $order = [];
    // 查询限制，如分页的limit。
    protected $limit;
    // 查询偏移量，用于分页。
    protected $offset;

    /**
     * 构造函数，初始化模型。
     *
     * @param Container $container 依赖注入容器。
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->sqlService = $container->resolve('SqlService');
        $this->logger = $container->resolve('LoggerService');
        $this->prefix = $this->sqlService->getPrefix();
        $this->softDeleteField = $this->sqlService->getSoftDeleteField();
    }

    /**
     * 记录日志。
     *
     * @param string $msg 日志信息。
     */
    public function log($msg)
    {
        $this->logger->log($msg, $this->logprefix[0]);
    }
    /**
     * 设置是否启用软删除。
     *
     * @param bool $enabled 是否启用软删除。
     * @return ModelInterface
     */
    public function withSoftDeletes(bool $enabled = true): bool
    {
        $this->softDeletes = $enabled;
        return $this;
    }

    /**
     * 设置数据表名称。
     *
     * @param string $table 数据表名称。
     * @return
     */
    public function setTable(string $table): string
    {
        return $this->sqlService->setTable($table);
    }

    /**
     * 设置查询条件。
     *
     * @param array $conditions 查询条件。
     * @return ModelInterface
     */
    public function where(array $conditions)
    {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * 设置查询字段。
     *
     * @param array $fields 查询字段。
     * @return ModelInterface
     */
    public function select(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * 添加join操作。
     *
     * @param string $table 加入的数据表。
     * @param string $condition join的条件。
     * @param string $type join的类型，默认为INNER。
     * @return ModelInterface
     */
    public function join(string $table, string $condition, string $type = 'INNER')
    {
        $this->joins[] = [$table, $condition, $type];
        return $this;
    }

    /**
     * 设置排序。
     *
     * @param string $field 排序的字段。
     * @param string $direction 排序的方向，默认为ASC。
     * @return ModelInterface
     */
    public function orderBy(string $field, string $direction = 'ASC')
    {
        $this->order[] = [$field, $direction];
        return $this;
    }

    /**
     * 设置查询的限制数量。
     *
     * @param int $limit 查询的限制数量。
     * @return ModelInterface
     */
    public function limit(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * 设置查询的偏移量。
     *
     * @param int $offset 查询的偏移量。
     * @return ModelInterface
     */
    public function offset(int $offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * 执行查询操作，返回查询结果。
     *
     * @return array 查询结果。
     */
    public function find(): array
    {
        if ($this->softDeletes) {
            $this->conditions[$this->softDeleteField] = null;
        }
        try {
            $result = $this->sqlService->select(
                $this->table,
                $this->fields,
                $this->conditions,
                $this->joins,
                $this->order,
                $this->limit,
                $this->offset
            );
            $this->reset();
            return $result ?: [];
        } catch (PDOException $e) {
            $this->log(ModelInterface::class . ' ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 插入数据。
     *
     * @param array $data 要插入的数据。
     * @return bool 插入操作的结果。
     */
    public function insert(array $data): bool
    {
        $this->beforeSave($data);
        try {
            $result = $this->sqlService->insert($this->table, $data);
            $this->afterSave();
            return $result;
        } catch (PDOException $e) {
            $this->log(ModelInterface::class . ' ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 更新数据。
     *
     * @param array $data 要更新的数据。
     * @return bool 更新操作的结果。
     */
    public function update(array $data): bool
    {
        $this->beforeSave($data);
        try {
            $result = $this->sqlService->update($this->table, $data, $this->conditions);
            $this->afterSave();
            return $result;
        } catch (PDOException $e) {
            $this->log(ModelInterface::class . ' ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 删除数据。
     *
     * @return bool 删除操作的结果。
     */
    public function delete(): bool
    {
        if ($this->softDeletes) {
            return $this->update([$this->softDeleteField => date('Y-m-d H:i:s')]);
        }

        try {
            $result = $this->sqlService->delete($this->table, $this->conditions);
            $this->reset();
            return $result;
        } catch (PDOException $e) {
            $this->log(ModelInterface::class . ' ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 在保存数据前的钩子函数，可用于数据验证和清理。
     *
     * @param array &$data 要保存的数据。
     */
    public function beforeSave(array &$data): void
    {
        // 数据验证和清理示例
        // foreach ($data as $key => $value) {
        //     if (is_string($value)) {
        //         $data[$key] = trim($value); // 去除多余的空格
        //     }
        // }

        // 假设我们需要验证 email 字段
        // if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        //     throw new \InvalidArgumentException("Invalid email format");
        // }
    }

    /**
     * 在数据保存后的钩子函数，可用于清理缓存或发送通知等操作。
     */
    public function afterSave(): void
    {
        // 假设我们需要清理缓存
        // $cacheService = $this->container->resolve('CacheService');
        // $cacheService->clearCache($this->table);

        // 假设我们需要发送通知
        // $notificationService = $this->container->resolve('NotificationService');
        // $notificationService->sendNotification("Data saved in {$this->table}");
    }

    /**
     * 重置查询条件和选项，用于连续查询。
     */
    public function reset(): void
    {
        $this->conditions = [];
        $this->fields = ['*'];
        $this->joins = [];
        $this->order = [];
        $this->limit = null;
        $this->offset = null;
    }
}
