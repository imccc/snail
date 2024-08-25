<?php

namespace Imccc\Snail\Interfaces;

interface PluginInterface
{
    /**
     * 获取插件名称
     *
     * @return string 插件名称
     */
    public function getName(): string;

    /**
     * 在注册插件时执行的操作
     *
     * @param Container $container 容器对象
     * @return void
     */
    public function onRegister(Container $container): void;

    /**
     * 在启用插件时执行的操作
     *
     * @param Container $container 容器对象
     * @return void
     */
    public function onEnable(Container $container): void;

    
    /**
     * 在禁用插件时执行的操作
     *
     * @param Container $container 容器对象
     * @return void
     */
    public function onDisable(Container $container): void;

    /**
     * 在卸载插件时执行的操作
     *
     * @param Container $container 容器对象
     * @return void
     */
    public function onUninstall(Container $container): void;

    /**
     * 获取插件依赖
     *
     * @return array
     */
    public function getDependencies(): array;

    /**
     * 获取插件配置
     *
     * @return array
     */
    public function getConfig(): array;

    /**
     * 日志
     */
    public function log($msg, $prefix = 'info'): void;
}
