<?php

namespace Imccc\Snail\Mvc;

interface IView
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

    /**
     * 缓存视图
     * @param string $tpl
     */
    public function cache($tpl = null);

    /**
     * 渲染模板
     * @param string $template
     * @param array $datas
     */
    public function render($template, $datas = []);

    /**
     * 处理include模板
     *
     * @param string $template
     * @param array $datas
     */
    public function includeTemplate($template, $datas = []);
}
