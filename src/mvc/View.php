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
    protected $_deftpl;
    protected $_ext;
    protected $_data = []; // 将 _datas 改为 _data
    protected $_cache;

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
        if (empty($tpl)) {
            $tpl = $this->_deftpl;
        }
        $fulltpl = $this->resolveTemplatePath($tpl);
        $this->logger->log('渲染视图：' . $tpl, $this->logprefix[0]);
        return $this->renderTemplate($fulltpl);
    }

    /**
     * 缓存视图
     * @param string $tpl
     */
    public function cache($tpl = null)
    {
        $this->logger->log('缓存视图：' . $tpl, $this->logprefix[0]);
        $this->_cache = $this->renderTemplate($tpl);
        return $this;
    }

    /**
     * 渲染模板
     * @param string $template
     * @param array $datas
     */
    public function render($template, $datas = [])
    {
        if (!empty($datas)) {
            $this->_data = array_merge($this->_data, $datas);
        }
        $this->logger->log('渲染模板：' . $template, $this->logprefix[0]);
        // 解析模板文件路径
        $templatePath = $this->resolveTemplatePath($template);

        // 渲染模板
        return $this->renderTemplate($templatePath, $datas);
    }

    /**
     * 解析模板文件路径，将相对路径转换为绝对路径
     *
     * @param string $template 相对路径的模板文件
     * @return string 绝对路径的模板文件
     */
    private function resolveTemplatePath($tpl = null)
    {
        // 如果 $tpl 参数为空，则使用默认模板文件名
        if (empty($tpl)) {
            $tpl = $this->_deftpl;
        }

        // 构建完整的模板文件路径
        $absolutePath = $this->templatePath . DIRECTORY_SEPARATOR . $tpl . $this->_ext;

        // 返回完整的模板文件路径
        return $absolutePath;
    }

    /**
     * 渲染模板
     * @param string $template
     */
    /**
     * 渲染模板
     * @param string $template
     */
    private function renderTemplate($template)
    {
        $templateFile = $this->templatePath . $template . '.php';
        if (file_exists($templateFile)) {
            // 读取模板文件内容
            $templateContent = file_get_contents($templateFile);

            // 解析模板标签
            $parsedTemplate = $this->parseTemplate($templateContent, $this->templateTags);

            // 使用一个关联数组来保存模板数据
            $templateData = $this->_data;

            // 将模板数据导入当前符号表，以便在模板中直接访问
            extract($templateData);

            // 开启输出缓冲
            ob_start();

            // 包含模板文件
            include $templateFile;

            // 获取缓冲区内容并清空缓冲区
            $content = ob_get_clean();
            $this->logger->log('渲染模板：' . $template, $this->logprefix[0]);
            // 返回渲染后的内容
            return $content;
        } else {
            $info = '模板文件不存在：' . $templateFile;
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
        foreach ($tags as $tag => $replacement) {
            if ($tag === 'include_file %%') {
                // 匹配 include_file %% 标签
                $pattern = '/' . str_replace('%%', '(.+)', preg_quote($tag, '/')) . '/';
                $template = preg_replace_callback($pattern, function ($matches) {
                    // 获取 include 文件的相对路径
                    $relativePath = trim($matches[1]);

                    // 获取当前模板文件所在目录
                    $currentDirectory = dirname($_SERVER['SCRIPT_FILENAME']);

                    // 解析相对路径为绝对路径
                    $absolutePath = $currentDirectory . DIRECTORY_SEPARATOR . $relativePath;

                    // 返回解析后的 include 标签
                    return '<?php include "' . $absolutePath . '";?>';
                }, $template);
            } else {
                // 对其他标签进行正常替换
                $pattern = '/' . str_replace('%%', '(.+)', preg_quote($tag, '/')) . '/';
                $template = preg_replace($pattern, $replacement, $template);
            }
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
        $info .= print_r($this->tplconf, true);
        $info .= '</pre>';
        ExceptionHandlerTrait::showDebug($info);
    }

}
