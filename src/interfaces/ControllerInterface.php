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
     * 显示视图
     *
     * @param string $tpl 模版信息，可以为空
     * @return void
     */
    public function display($tpl = '');

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

}
