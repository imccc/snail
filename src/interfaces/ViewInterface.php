<?php

namespace Imccc\Snail\Interfaces;

interface ViewInterface
{
    /**
     * 分配数据给视图
     *
     * @param string|array $key 参数键名或参数数组
     * @param mixed $value 参数值（仅在第一个参数为键名时有效）
     */
    public function assign($key, $value = null): void;

    /**
     * 显示视图
     * @param string $tpl
     * @return string
     */
    public function display($tpl = null);

   
}
