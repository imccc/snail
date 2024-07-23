<?php

namespace Imccc\Snail\Mvc;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Interfaces\ViewInterface;
use Imccc\Snail\Core\Debug;

class View implements ViewInterface
{
    protected $container;
    protected $config;
    protected $logger;
    protected $logprefix = ['view', 'error', 'debug'];
    protected $tplconf;
    protected $tplservice;
    protected $_data = [];
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $container->resolve('ConfigService');
        $this->logger = $container->resolve('LoggerService');

        $this->tplconf = $this->config->get('template');
        $this->_deftpl = $this->tplconf['default'] ?? 'index';
        $this->tplservice = $container->resolve('TemplateService');
    }

    /**
     * 赋值数据
     * @param string $key
     * @param string $value
     * @return void
     */
    public function assign($key, $value = null): void
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->_data[$k] = $v;
            }
        } else {
            $this->_data[$key] = $value;
        }
    }

    /**
     * 渲染视图
     * @param string $tpl
     * @return string
     */
    public function render()
    {
        extract($this->_data);
        $tpl = $tplpath . $routes['action'];
        return $this->tplservice->render($tpl, $this->_data);
    }

    /**
     * 显示视图
     * @param string $tpl
     * @return string
     */
    public function display()
    {
        echo $this->render();
    }

}
