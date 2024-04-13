<?php

namespace Imccc\Snail\Mvc;

interface IController
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
     * 添加调试信息。
     *
     * @param string $info 调试信息
     * @return void
     */
    public function debug(string $info): void;

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
     * 获取POST数据。
     *
     * @return array
     */
    public function getPost();

    /**
     * 验证用户权限。
     *
     * @param string $action 要执行的操作
     * @return bool
     */
    public function checkPermission(string $action): bool;

}
