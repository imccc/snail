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
    protected $_data = []; // 将 _datas 改为 _data

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
        if (DEBUG['view'] && DEBUG['debug']) {
            register_shutdown_function([self, 'debug']);
        }
    }

    /**
     * 分配数据给视图
     *
     * @param string|array $key 参数键名或参数数组
     * @param mixed $value 参数值（仅在第一个参数为键名时有效）
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
