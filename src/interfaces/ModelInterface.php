<?php

namespace Imccc\Snail\Interfases;

interface ModelInterface
{
    /**
     * 设置软删除
     * @param bool $enabled
     * @return $this
     */
    public function withSoftDeletes(bool $enabled = true): self;

    /**
     * 设置模型表名
     * @param string $table
     * @return $this
     */
    public function setModel(string $table): self;

    /**
     * 设置查询条件
     * @param array $conditions
     * @return $this
     */
    public function where(array $conditions): self;

    /**
     * 设置查询字段
     * @param array $fields
     * @return $this
     */
    public function select(array $fields): self;

    /**
     * 查询数据
     * @return array
     */
    public function find(): array;

    /**
     * 插入数据
     * @param array $data
     * @return bool
     */
    public function insert(array $data): bool;

    /**
     * 更新数据
     * @param array $data
     * @return bool
     */
    public function update(array $data): bool;

    /**
     * 删除数据
     * @return bool
     */
    public function delete(): bool;
}
