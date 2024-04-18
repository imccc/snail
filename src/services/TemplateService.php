<?php

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Services\Engines\SnailEngine;
use Imccc\Snail\Services\Engines\TwigEngine;
use Imccc\Snail\Traits\DebugTrait;
use Imccc\Snail\Traits\HandleExceptionTrait;

class TemplateService
{
    protected $config;
    protected $logger;
    protected $container;
    protected $logprefix = ['template', 'error'];
    protected $engine;

    use DebugTrait,HandleExceptionTrait;
    public function __construct(Container $container)
    {
        // 注册全局异常处理函数
        set_error_handler([self::class, 'handleException']);

        $this->container = $container;
        $this->config = $this->container->resolve('ConfigService');
        $this->logger = $this->container->resolve('LoggerService');
        $this->engine = $this->config->get('template.engine') ?? 'snail';

        if (DEBUG['debug'] && DEBUG['template']) {
            register_shutdown_function([self::class, 'debug']);
        }
    }

    public function setEngine($engine)
    {
        switch ($engine) {
            case 'twig':
                $this->engine = new TwigEngine($this->container);
                break;
            case 'snail':
            default:
                $this->engine = new SnailEngine($this->container);
                break;
        }
    }

    /**
     * 显示模板
     *
     * @param string $tpl 模板文件路径
     * @param array $data 渲染模板时所需的数据
     * @return void
     */
    public function display($tpl, $data = [])
    {
        $this->setEngine($this->engine);
        self::bindDebugInfo('template', $tpl);
        $content = $this->engine->render($tpl, $data);
        return $content;
    }

    /**
     * 渲染模板
     *
     * @param string $tpl 模板文件路径
     * @param array $data 渲染模板时所需的数据
     * @return string 渲染后的模板内容
     */
    public function render($tpl, $data = [])
    {
        return $this->engine->render($tpl, $data);
    }

    /**
     * 缓存模板
     *
     * @param string $tpl 模板文件路径
     * @param array $data 渲染模板时所需的数据
     * @return void
     */
    public function cache($tpl, $data = [])
    {
        $this->engine->cache($tpl, $data);
        self::bindDebugInfo('cache', 'Snail Template Cache Success');
    }

}
