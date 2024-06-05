<?php

namespace Imccc\Snail\Mvc;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Interfaces\ViewInterface;
use Imccc\Snail\Traits\DebugTrait;
use Imccc\Snail\Traits\HandleExceptionTrait;

class View implements ViewInterface
{
    use HandleExceptionTrait, DebugTrait;
    protected $container;
    protected $config;
    protected $logger;
    protected $logprefix = ['view', 'error'];
    protected $tplconf;
    protected $templatePath;
    protected $templateTags;
    private $_debuginfo = [];
    protected $_deftpl;
    protected $_ext;
    protected $_data = [];
    public function __construct(Container $container,$data)
    {
        $this->container = $container;
        $this->_data = $data;
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
     * 显示视图
     * @param string $tpl
     * @return string
     */
    public function display($tpl = null)
    {
        $fullpath = $tpl . $this->_ext;
        self::bindDebugInfo('displayFullpath', $fullpath);
        $this->_data['title'] = SNAIL . ' - ' . SNAIL_VERSION;
        $this->engine->display($fullpath, $this->_data);
    }

}
