<?php

namespace Imccc\Snail\Services\Engines;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Traits\DebugTrait;
use Imccc\Snail\Traits\HandleExceptionTrait;

class SnailEngine
{
    use DebugTrait, HandleExceptionTrait;
    protected $container;
    protected $config;
    protected $cache;
    protected $content;
    protected $logger;
    protected $logprefix = ['template', 'error'];
    protected $_debuginfo = [];
    protected $templateConfig;

    public function __construct(Container $container)
    {
        set_error_handler([self::class, 'handleException']);
        $this->container = $container;
        $this->config = $container->resolve('ConfigService');
        $this->cache = $container->resolve('CacheService');
        $this->logger = $container->resolve('LoggerService');
        $this->templateConfig = $this->config->get('template');

        if (DEBUG['debug'] && DEBUG['engine']) {
            register_shutdown_function([$this, 'debug']);
        }
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
        // 构建模板文件路径
        // $tplPath = $this->templateConfig['path'] . $tpl;
        $tplPath = $tpl;
        $this->_debuginfo['SnailEngine']['render']['tplPath'] = $tplPath;

        // 判断是否启用缓存
        if ($this->templateConfig['cache']) {
            $cacheKey = md5($tplPath);
            $content = $this->cache->get($cacheKey);

            if (!$content) {
                // 缓存不存在，解析模板并存储到缓存中
                $content = $this->parse($tplPath, $data);
                $this->cache->set($cacheKey, $content);
            }
        } else {
            // 不使用缓存，直接解析模板
            $content = $this->parse($tplPath, $data);
        }
        // 记录渲染成功日志
        $this->logger->log('Snail Template Render Success', $this->logprefix[0]);
        return $content;
    }

    /**
     * 解析模板
     *
     * @param string $tplPath 模板文件路径
     * @param array $data 渲染模板时所需的数据
     * @return string 渲染后的模板内容
     */
    protected function parse(string $tplPath, array $data): string
    {
        // 加载模板文件内容
        $content = $this->loadTemplate($tplPath);

        // 解析模板继承和片段
        $content = $this->parseTemplateInheritance($content);

        // 解析自定义标签
        $content = $this->parseCustomTags($content);

        // 替换模板变量
        $content = $this->replaceVariables($content, $data);

        // 替换模板块
        $content = $this->replaceBlocks($content);

        // 解析静态资源标签
        $content = $this->parseAssetTags($content);

        return $content;
    }

    /**
     * 加载模板文件内容
     *
     * @param string $tplPath 模板文件路径
     * @return string 模板文件内容
     */
    protected function loadTemplate(string $tplPath): string
    {
        if (!file_exists($tplPath)) {
            // 记录模板文件不存在错误日志
            $this->_debuginfo['SnailEngine']['loadTemplate']['tplPath'] = $tplPath;
            $this->logger->log('Snail Template File Not Found', $this->logprefix[1]);
            throw new \RuntimeException("Template file not found: $tplPath");
        }

        return file_get_contents($tplPath);
    }

    /**
     * 解析模板继承和片段
     *
     * @param string $content 待解析的模板内容
     * @return string 解析后的模板内容
     */
    protected function parseTemplateInheritance(string $content): string
    {
        // 解析继承标签
        $content = preg_replace_callback('/{%\s*extends\s+"([^"]+)"\s*%}/', function ($matches) {
            $parentTemplate = $this->loadTemplate($matches[1]);
            return $this->parseTemplateBlocks($parentTemplate);
        }, $content);

        // 解析片段标签
        $content = preg_replace_callback('/{%\s*block\s+([^%]+)%}(.*?)\{%\s*endblock\s*%}/s', function ($matches) {
            $this->blocks[$matches[1]] = $matches[2];
            return '';
        }, $content);

        return $this->parseTemplateBlocks($content);
    }

    /**
     * 解析模板中的块
     *
     * @param string $content 待解析的模板内容
     * @return string 解析后的模板内容
     */
    protected function parseTemplateBlocks(string $content): string
    {
        if (empty($this->blocks)) {
            return $content;
        }

        return preg_replace_callback('/{%\s*block\s+([^%]+)%}(.*?)\{%\s*endblock\s*%}/s', function ($matches) {
            if (isset($this->blocks[$matches[1]])) {
                return str_replace($matches[0], $this->blocks[$matches[1]], $this->blocks[$matches[1]]);
            }
            return '';
        }, $content);
    }

    /**
     * 解析自定义标签
     *
     * @param string $content 待解析的模板内容
     * @return string 解析后的模板内容
     */
    protected function parseCustomTags(string $content): string
    {
        // 获取标签配置
        $tags = $this->templateConfig['tag'] ?? [];

        // 循环处理每个标签
        foreach ($tags as $tag => $replacement) {
            // 构建正则表达式
            $regex = '/' . str_replace('%%', '(.*?)', preg_quote($tag, '/')) . '/';

            // 使用正则表达式替换标签
            $content = preg_replace_callback($regex, function ($matches) use ($replacement) {
                // 替换标签中的占位符
                $replacement = preg_replace('/\\(\d+)/', '$matches[$1]', $replacement);
                return $replacement;
            }, $content);
        }

        return $content;
    }

    /**
     * 替换模板变量
     *
     * @param string $content 待替换的模板内容
     * @param array $data 渲染模板时所需的数据
     * @return string 替换后的模板内容
     */
    protected function replaceVariables(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            $content = str_replace("{{ $key }}", $value, $content);
        }

        return $content;
    }

    /**
     * 运行模板中的函数
     *
     * @param string $content 待运行的模板内容
     * @param array $data 渲染模板时所需的数据
     * @return string 替换后的模板内容
     */
    protected function runFunctions(string $content, array $data): string
    {
        // 执行函数标签
        $content = preg_replace_callback('/{{\s*(\w+)\((.*?)\)\s*}}/', function ($matches) use ($data) {
            // 获取函数名和参数
            $functionName = $matches[1];
            $rawArguments = $matches[2];

            // 解析参数
            $arguments = $this->parseFunctionArguments($rawArguments, $data);

            // 检查函数是否存在
            if (function_exists($functionName)) {
                $result = call_user_func_array($functionName, $arguments);
            } else {
                $this->logger->log("Snail Template Function Error: Function $functionName does not exist.", $this->logprefix[1]);
                $result = "Function $functionName does not exist.";
            }

            return $result;
        }, $content);

        return $content;
    }

    /**
     * 解析函数参数
     *
     * @param string $rawArguments 原始参数字符串
     * @param array $data 渲染模板时所需的数据
     * @return array 解析后的参数数组
     */
    protected function parseFunctionArguments(string $rawArguments, array $data): array
    {
        $arguments = [];

        // 解析逗号分隔的参数
        $commaSeparatedArguments = explode(',', $rawArguments);
        foreach ($commaSeparatedArguments as $argument) {
            // 去除参数两边的空格，并替换模板变量
            $argument = str_replace(["{{", "}}"], "", trim($argument));

            // 如果参数包含引号，则不进行模板变量替换
            if (preg_match('/^\s*["\'].*["\']\s*$/', $argument)) {
                $arguments[] = $argument;
            } else {
                // 否则进行模板变量替换
                $arguments[] = $data[$argument] ?? null;
            }
        }

        return $arguments;
    }

    /**
     * 解析静态资源标签
     *
     * @param string $content 待解析的模板内容
     * @return string 解析后的模板内容
     */
    protected function parseAssetTags(string $content): string
    {
        // 解析 {{ js:vendor|type }} 和 {{ css:vendor|type }} 标签
        $content = preg_replace_callback('/{{\s*(js|css):(\w+)\|(\w+)\s*}}/', function ($matches) {
            $type = $matches[1]; // js 或 css
            $vendor = $matches[2]; // 厂商名称
            $assetType = $matches[3]; // 资源类型

            // 获取静态资源路径
            $assetPath = $this->getVendorAssetPath($vendor, $assetType);

            // 构建标签内容
            $tag = ($type === 'js') ? '<script src="' : '<link rel="stylesheet" href="';
            $tag .= $assetPath . '">';

            return $tag;
        }, $content);

        return $content;
    }

  

}
