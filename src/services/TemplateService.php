<?php

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Services\Engines\SnailEngine;
use Imccc\Snail\Services\Engines\TwigEngine;

class TemplateService
{
    protected $config;
    protected $logger;
    protected $container;
    protected $logprefix = ['template', 'error'];
    protected $_debuginfo=[];
    protected $engine;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $this->container->resolve('ConfigService');
        $this->logger = $this->container->resolve('LoggerService');
        $this->engine = $this->coxnfig->get('template.engine') ?? 'snail';
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
    public function display($tpl,  $data = [])
    {
        $this->setEngine($this->engine);
        $this->_debuginfo['Template']['tplpata'] = $tpl;
        $content = $this->engine->render($tpl, $data);
        $this->logger->log('Snail Template Display Success', $this->logprefix[0]);
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
        $this->logger->log('Snail Template Cache Success', $this->logprefix[0]);
    }

}
