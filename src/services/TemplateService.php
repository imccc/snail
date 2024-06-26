<?php

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Services\Engines\SnailEngine;
use Imccc\Snail\Services\Engines\TwigEngine;
use Imccc\Snail\Traits\DebugTrait;

class TemplateService
{
    protected $config;
    protected $logger;
    protected $container;
    protected $engine;
    protected $_engine;
    protected $logprefix = ['template', 'error'];

    use DebugTrait;
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $this->container->resolve('ConfigService');
        $this->logger = $this->container->resolve('LoggerService');
        $this->_engine = $this->config->get('template.engine') ?? 'snail';
        $this->setEngine($this->_engine);
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
        self::bindDebugInfo('display_template', $tpl);
        $content = $this->engine->render($tpl, $data);
        echo $content;
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
        self::bindDebugInfo('render_template', $tpl);
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
