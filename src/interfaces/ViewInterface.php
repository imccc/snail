<?php

namespace Imccc\Snail\Interfaces;

interface ViewInterface
{

    /**
     * 赋值数据
     * @param string $key 数据键
     * @param mixed $value 数据值
     * @return void
     */
    public function assign($key, $value = null): void;

    /**
     * 渲染视图
     * @param string $tpl
     * @return string
     */
    public function render();

    /**
     * 显示视图
     * @param string $tpl
     * @return string
     */
    public function display();

    /**
     * 显示视图
     * @param string $tpl
     * @return string
     */
    public function show($tplpath,$data);
   
}
