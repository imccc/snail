<?php

namespace Imccc\Snail\Interfaces;

interface ControllerInterface
{
    /**
     * 获取与控制器关联的模型实例。
     *
     * @return mixed
     */
    public function getModel();

    /**
     * 获取与控制器关联的视图实例。
     *
     * @return mixed
     */
    public function getView();

    /**
     * 生成API方法
     * @return void
     */
    public function getApi();

    /**
     * 显示API
     *
     * @param [type] $oridata
     * @return void
     */
    public function api($oridata);

    /**
     * 显示视图
     *
     * @param string $tpl 模版信息，可以为空
     * @return void
     */
    public function display($tpl = '');

    /**
     * 根据请求处理数据，如果是ajax请求则返回api数据，否则显示视图
     * @return void
     */
    public function fetch($data);

    /**
     * 将数据分配到视图。
     *
     * @param string $key 数据键
     * @param mixed $value 数据值
     * @return void
     */
    public function assign(string $key, $value): void;

    /**
     * 处理输入参数。
     *
     * @param string $param 参数名称
     * @return mixed
     */
    public function input(string $param);

    /**
     * 对输入参数进行清理和验证。
     *
     * @param mixed $input 输入参数
     * @return mixed
     */
    public function sanitizeInput($input);

    /**
     * 验证用户权限。
     *
     * @param string $action 要执行的操作
     * @return bool
     */
    public function checkPermission(string $action): bool;

    /**
     * 判断是否AJAX请求
     * @return bool
     */
    public function isAjax(): bool;
}
