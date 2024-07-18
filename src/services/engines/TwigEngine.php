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
    protected $templateConfig;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $container->resolve('ConfigService');
        $this->logger = $container->resolve('LoggerService');
        $this->templateConfig = $this->config->get('template');

        // 初始化 Twig 环境
        $loader = new FilesystemLoader(); // 初始化空的 Twig 加载器
        $twigConfig = $this->templateConfig['twig']['options'] ?? [];
        $this->twig = new Environment($loader, $twigConfig);
    }

    /**
     * 渲染 Twig 模板
     *
     * @param string $tpl 模板文件的绝对路径
     * @param array $data 渲染模板时所需的数据
     * @return string 渲染后的模板内容
     * @throws \Exception 如果模板文件未找到
     */
    public function render(string $tpl, array $data = []): string
    {
        
        $tpl .= $this->templateConfig['twig']['ext'];
        try {
            // 获取模板目录和文件名
            $templateDir = dirname($tpl);
            $templateFile = basename($tpl);
            $this->logger->log(self::class ." : [". __FUNCTION__ . '] : Template file: ' . $tpl, $this->logprefix[0]);

            // 动态设置 Twig 加载路径
            $this->twig->getLoader()->addPath($templateDir);

            // 渲染模板
            return $this->twig->render($templateFile, $data);
        } catch (\Exception $e) {
            throw new \Exception("Template file not found or rendering error: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
