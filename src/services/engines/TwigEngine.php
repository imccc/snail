<?php

namespace Imccc\Snail\Services\Engines;

use Imccc\Snail\Core\Container;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigEngine
{
    protected $twig;
    protected $container;
    protected $logprefix = ['template', 'error'];
    protected $config;
    protected $logger;

    public function __construct(Container $container)
    {
        // Twig 模板文件目录
        $templatePath = $container->resolve('ConfigService')->get('template.path');

        // Twig 加载器
        $loader = new FilesystemLoader($templatePath);

        // Twig 配置
        $twigConfig = [];

        // 创建 Twig 环境
        $this->twig = new Environment($loader, $twigConfig);
    }

    /**
     * 渲染 Twig 模板
     *
     * @param string $tpl 模板文件路径
     * @param array $data 渲染模板时所需的数据
     * @return string 渲染后的模板内容
     */
    public function render(string $tpl, array $data = []): string
    {
        return $this->twig->render($tpl, $data);
    }
}
