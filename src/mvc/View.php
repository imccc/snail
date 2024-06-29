<?php

namespace Imccc\Snail\Mvc;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Interfaces\ViewInterface;

class View implements ViewInterface
{
    protected $container;
    protected $config;
    protected $logger;
    protected $logprefix = ['view', 'error','debug'];
    protected $tplconf;
    protected $templatePath;
    protected $templateTags;
    protected $_deftpl;
    protected $_ext;
    protected $_data = [];
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $container->resolve('ConfigService');
        $this->logger = $container->resolve('LoggerService');

        $this->tplconf = $this->config->get('template');
        $this->templatePath = $this->tplconf['path'];
        $this->templateTags = $this->tplconf['tags'];
        $this->_deftpl = $this->tplconf['default'] ?? 'index';
        $this->_ext = $this->tplconf['ext'] ?? '.tpl';

        $this->engine = $container->resolve('TemplateService');
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
        $fullpath = $tpl . $this->_ext;
        $this->logger->log(self::class . ' View render fullpath: ' . $fullpath, $this->logprefix[2]);
        return $this->engine->render($fullpath);
    }

    /**
     * 显示视图
     * @param string $tpl
     * @return string
     */
    public function display()
    {
        $this->logger->log(self::class . ' View display fullpath: ' . $fullpath, $this->logprefix[2]);
        echo $this->render();
    }

}
 