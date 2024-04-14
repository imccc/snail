<?php

namespace Imccc\Snail\Mvc;

use Exception;
use Imccc\Snail\Core\Container;
use Imccc\Snail\Interfaces\ViewInterface;
use Imccc\Snail\Traits\ExceptionHandlerTrait;

class View implements ViewInterface
{
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
    protected $_cache;
    protected $_engine;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $container->resolve('ConfigService');
        $this->logger = $container->resolve('LoggerService');

        $this->tplconf = $this->config->get('template');
        $this->templatePath = $this->tplconf['path'];
        $this->templateTags = $this->tplconf['tags'];
        $this->_deftpl = $this->tplconf['default'];
        $this->_ext = $this->tplconf['ext'];

        $this->engine = $container->resolve('TemplateService');
        if (DEBUG['view'] && DEBUG['debug']) {
            register_shutdown_function([$this, 'debug']);
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
        $this->engine->display($fullpath, $this->_data);
        // echo $this->renderTemplate($fullpath);
    }

    /**
     * 渲染模板
     * @param string $template
     */
    private function renderTemplate($fullpath)
    {
        $this->_debuginfo['fullpath'] = $fullpath;
        if (file_exists($fullpath)) {
            // 读取模板文件内容
            $templateContent = file_get_contents($fullpath);

            // 解析模板标签
            $parsedTemplate = $this->parseTemplate($templateContent, $this->templateTags);

            // 使用一个关联数组来保存模板数据
            $templateData = $this->_data;

            // 将模板数据导入当前符号表，以便在模板中直接访问
            extract($templateData);

            // 开启输出缓冲
            ob_start();

            // 包含模板文件
            include $fullpath;

            // 获取缓冲区内容并清空缓冲区
            $content = ob_get_clean();
            // 返回渲染后的内容
            return $content;
        } else {
            $info = '模板文件不存在：' . $fullpath;
            $this->logger->log($info, $this->logprefix[1]);
            $this->handleException(new Exception($info));
        }

    }

    /**
     * 处理include模板
     *
     * @param string $template
     * @param array $datas
     */
    public function includeTemplate($template, $datas = [])
    {
        if (!empty($datas)) {
            $this->_data = array_merge($this->_data, $datas);
        }
        $this->logger->log('渲染模板：' . $template, $this->logprefix[0]);
        return $this->renderTemplate($template);
    }

    /**
     * 正则匹配并解析模板标签，包括 include 文件的相对路径
     *
     * @param string $template 模板内容
     * @param array $tags 模板标签映射数组
     * @return string 解析后的模板内容
     */
    public function parseTemplate($template, $tags)
    {
        // 处理 include 文件
        $template = preg_replace_callback(
            '/{{\s*include_file (.+?)\s*}}/',
            function ($matches) {
                // 获取 include 文件的相对路径
                $relativePath = trim($matches[1]);

                // 获取当前模板文件所在目录
                $currentDirectory = dirname($_SERVER['SCRIPT_FILENAME']);

                // 解析相对路径为绝对路径
                $absolutePath = $currentDirectory . DIRECTORY_SEPARATOR . $relativePath;

                // 返回解析后的 include 标签
                return '<?php include "' . $absolutePath . '"; ?>';
            },
            $template
        );

        // 替换普通模板标签
        foreach ($tags as $tag => $replacement) {
            $template = preg_replace('/{{\s*' . preg_quote($tag, '/') . '\s*}}/', '<?php echo ' . $replacement . '; ?>', $template);
        }

        $this->logger->log('解析模板标签：' . $template, $this->logprefix[0]);
        return $template;
    }

    /**
     * 异常处理函数
     */
    protected function handleException(Exception $e): void
    {
        ExceptionHandlerTrait::handleException($e);
    }

    /**
     * 添加调试信息。
     *
     * @return void
     */
    public function debug(): void
    {
        $info = "<h3>以下信息由 类: " . self::class . " 提供<small>@ " . date("Y-m-d H:i:s.u") . "</small></h3>";
        $info .= '<pre>';
        $info .= print_r($this->_debuginfo, true);
        $info .= '</pre>';
        ExceptionHandlerTrait::showDebug($info);
    }

}
