<?php

namespace Imccc\Snail\Interfaces;

/**
 * 数据模型接口
 *
 * 该接口定义了数据操作的基本方法，包括查询、插入、更新和删除数据的能力。
 * 同时，提供了软删除特性开关、数据表设置、查询条件、排序、分页等操作的方法。
 */
interface ModelInterface
{
    /**
     * 日志方法
     */
    public function log($message);
    
    /**
     * 启用或禁用软删除特性。
     *
     * 该方法允许开发者在运行时启用或禁用软删除特性。软删除是一种设计模式，
     * 用于在不实际从数据库中删除记录的情况下，使记录看起来像是被删除了。
     * 这对于那些需要恢复已删除数据的情况非常有用。
     *
     * @param bool $enabled 默认为true，表示启用软删除。如果设置为false，则禁用软删除。
     * @return bool 返回对象自身，支持链式调用。
     */
    public function withSoftDeletes(bool $enabled = true): bool;

    /**
     * 设置数据表名称
     *
     * @param string $table 数据表名称
     * @return string
     */
    public function setTable(string $table): string;

    /**
     * 设置查询条件
     *
     * @param array $conditions 查询条件，键值对形式，键为字段名，值为字段值
     * @return 
     */
    public function where(array $conditions);

    /**
     * 设置查询字段
     *
     * @param array $fields 需要查询的字段名数组
     * @return 
     */
    public function select(array $fields);

    /**
     * 执行 JOIN 查询
     *
     * @param string $table 加入的数据表名称
     * @param string $condition JOIN 的条件
     * @param string $type JOIN 类型，默认为 INNER JOIN
     * @return 
     */
    public function join(string $table, string $condition, string $type = 'INNER');

    /**
     * 设置查询结果的排序
     *
     * @param string $field 排序的字段名
     * @param string $direction 排序方向，默认为 ASC
     * @return 
     */
    public function orderBy(string $field, string $direction = 'ASC');

    /**
     * 设置查询结果的限制数量
     *
     * @param int $limit 返回结果的数量限制
     * @return 
     */
    public function limit(int $limit);

    /**
     * 设置查询结果的偏移量
     *
     * @param int $offset 查询结果的起始偏移量
     * @return 
     */
    public function offset(int $offset);

    /**
     * 执行查询并返回结果集
     *
     * @return array 查询结果集
     */
    public function find(): array;

    /**
     * 插入数据
     *
     * @param array $data 待插入的数据，键值对形式，键为字段名，值为字段值
     * @return bool 插入操作的执行结果，true 表示成功，false 表示失败
     */
    public function insert(array $data);

    /**
     * 更新数据
     *
     * @param array $data 待更新的数据，键值对形式，键为字段名，值为字段值
     * @return bool 更新操作的执行结果，true 表示成功，false 表示失败
     */
    public function update(array $data);

    /**
     * 删除数据
     *
     * @return bool 删除操作的执行结果，true 表示成功，false 表示失败
     */
    public function delete();

    /**
     * 在数据保存前执行的钩子方法
     *
     * @param array &$data 待保存的数据，可以通过引用修改
     */
    public function beforeSave(array &$data): void;

    /**
     * 在数据保存后执行的钩子方法
     */
    public function afterSave(): void;

    /**
     * 重置查询条件和设置
     */
    public function reset(): void;

}
