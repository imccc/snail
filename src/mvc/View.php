<?php

namespace Imccc\Snail\Mvc;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Interfaces\ViewInterface;

class View implements ViewInterface
{
    protected $container;
    protected $config;
    protected $logger;
    protected $logprefix = ['view', 'error', 'debug'];
    protected $tplconf;
    protected $tplservice;
    protected $_data = [];
    protected $_engine;
    protected $_deftpl;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $container->resolve('ConfigService');
        $this->logger = $container->resolve('LoggerService');
        $this->tplservice = $container->resolve('TemplateService');

        $this->tplconf = $this->config->get('template');
        $this->_engine = $this->tplconf['engine'];
        $this->_deftpl = $this->tplconf['default'] ?? 'index';
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
     * @return string
     */
    public function render()
    {
        // print_r($this->_data);die;
        extract($this->_data);
        $tpl = $tplpath . $this->_engine . "/" . $routes['action'];
        if ($this->_engine != '' || $this->_engine != null) {
            return $this->tplservice->render($tpl, $this->_data);
        } else {
            return $this->show($tpl, $this->_data);
        }
    }

    /**
     * 显示视图
     * @return void
     */
    public function display()
    {
        echo $this->render();
    }

    /**
     * 显示视图
     * @param string $tplpath
     * @param array $data
     * @return string
     */
    public function show($tplpath, $data)
    {
        extract($data);

        // 构造视图文件路径
        $viewFile = $tplpath . $this->tplconf['view']['page'] . $routes['action'] . $this->tplconf['view']['ext'];
        if (file_exists($viewFile)) {
            ob_start();
            include $viewFile;
            $content = ob_get_clean();
            return $content;
        } else {
            $this->logger->log("View file not found: " . $viewFile, $this->logprefix[1]);
            throw new \RuntimeException("View file not found: " . $viewFile);
        }
    }
}
