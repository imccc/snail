<?php

namespace Imccc\Snail\Services\Engines;

use Imccc\Snail\Core\Container;

/**
 * SnailEngine类负责模板渲染。
 * 它使用依赖注入来获取配置、缓存和日志服务，并处理模板继承、块替换和变量替换。
 */
class SnailEngine
{
    // 依赖注入容器
    protected $container;
    // 配置服务
    protected $config;
    // 缓存服务
    protected $cache;
    // 日志服务
    protected $logger;
    // 日志前缀，用于不同类型的日志记录
    protected $logprefix = ['template', 'error', 'debug'];
    // 模板配置
    protected $templateConfig;
    // 模板块集合
    protected $blocks = [];
    // 模板路径
    protected $templatePath;
    // 模板标签
    protected $templateTags;

    /**
     * 构造函数初始化容器，并从容器中解析配置、缓存和日志服务。
     * 它还初始化模板配置和路径。
     *
     * @param Container $container 依赖注入容器
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $container->resolve('ConfigService');
        $this->cache = $container->resolve('CacheService');
        $this->logger = $container->resolve('LoggerService');

        $this->templateConfig = $this->config->get('template');
        $this->templatePath = $this->templateConfig['path'];
        $this->templateTags = $this->templateConfig['snail']['tags'];
    }

    /**
     * 渲染模板。
     * 如果启用了缓存，它将尝试从缓存中获取内容；否则，它将解析模板并缓存结果。
     * 如果模板路径未在数据数组中提供，则抛出异常。
     *
     * @param string $tpl 模板文件名
     * @param array $data 渲染模板时使用的数据
     * @return string 渲染后的模板内容
     * @throws \Exception 如果模板路径未提供或模板文件不存在
     */
    public function render($tpl, $data = [])
    {
        if (!isset($data['tplpath'])) {
            throw new \Exception("Template path (tplpath) not provided in data array.");
        }

        $tplPath = $tpl . $this->templateConfig['snail']['ext'];
        $this->log(self::class ." :: [". __FUNCTION__ . '] : Template render: ' . $tplPath, $this->logprefix[2]);

        if (!file_exists($tplPath)) {
            throw new \Exception("Template file not found: $tplPath");
        }

        if ($this->templateConfig['cache']) {
            $cacheKey = md5($tplPath);
            $content = $this->cache->get($cacheKey);

            if (!$content) {
                $content = $this->parse($tplPath, $data);
                $this->cache->set($cacheKey, $content);
            }
        } else {
            $content = $this->parse($tplPath, $data);
        }

        $this->log(self::class ." :: [". __FUNCTION__ . '] : Template Render Success: ' . $content, $this->logprefix[0]);

        $output = $this->includeTemplateContent($content, $data);

        return $output;
    }

    /**
     * 解析模板。
     * 这包括加载模板文件、处理模板继承、替换变量和标签，以及解析模板块。
     *
     * @param string $tplPath 模板文件路径
     * @param array $data 渲染模板时使用的数据
     * @return string 解析后的模板内容
     */
    protected function parse(string $tplPath, array $data): string
    {
        $content = $this->loadTemplate($tplPath);
        $content = $this->parseTemplateInheritance($content, dirname($tplPath));
        $content = $this->replaceVariables($content, $data);
        $content = $this->replaceTags($content);
        $content = $this->parseTemplateBlocks($content);

        return $content; // 使用 trim 函数消除前后空白
    }

    /**
     * 加载模板文件内容。
     * 如果文件不存在，抛出异常。
     *
     * @param string $tplPath 模板文件路径
     * @return string 模板文件内容
     * @throws \Exception 如果模板文件不存在
     */
    protected function loadTemplate(string $tplPath): string
    {
        if (!file_exists($tplPath)) {
            throw new \Exception("Template file not found: $tplPath");
        }
        return file_get_contents($tplPath);
    }

    /**
     * 处理模板继承。
     * 通过递归查找并替换父模板，直到没有继承关系为止。
     *
     * @param string $content 模板内容
     * @param string $tplDir 模板目录
     * @return string 继承后的模板内容
     */
    protected function parseTemplateInheritance(string $content, string $tplDir): string
    {
        $content = preg_replace_callback('/{{\s*extend\s+"([^"]+)"\s*}}/', function ($matches) use ($tplDir) {
            $parentTemplatePath = $tplDir . '/' . $matches[1];
            $parentTemplate = $this->loadTemplate($parentTemplatePath);
            $parentTemplate = $this->parseTemplateInheritance($parentTemplate, dirname($parentTemplatePath));
            $this->extractBlocks($parentTemplate);
            return $parentTemplate;
        }, $content);

        $this->extractBlocks($content);

        return $content;
    }

    /**
     * 提取模板中的块。
     * 将块内容存储在$blocks属性中，以便后续的块替换。
     *
     * @param string &$content 模板内容
     */
    protected function extractBlocks(string &$content): void
    {
        preg_match_all('/{{\s*block\s+(\w+)\s*}}(.*?){{\s*block_end\s*}}/s', $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $blockName = $match[1];
            if (!isset($this->blocks[$blockName])) {
                $this->blocks[$blockName] = $match[2];
            }
            $content = str_replace($match[0], $this->blocks[$blockName], $content);
            $this->blocks = [];
        }
    }

    /**
     * 替换模板中的变量。
     * 根据$data数组中的值，使用htmlspecialchars对字符串变量进行转义，对数组变量使用json_encode。
     *
     * @param string $content 模板内容
     * @param array $data 渲染模板时使用的数据
     * @return string 替换后的模板内容
     */
    protected function replaceVariables(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            $pattern = '/{{\s*' . preg_quote($key, '/') . '\s*}}/';
            if (is_string($value)) {
                $content = preg_replace($pattern, htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $content);
            } elseif (is_array($value)) {
                $content = preg_replace($pattern, json_encode($value), $content);
            } else {
                $content = preg_replace($pattern, (string) $value, $content);
            }
        }

        return $content;
    }

    /**
     * 替换模板中的标签。
     * 使用配置中的标签替换映射来替换模板中的标签。
     *
     * @param string $content 模板内容
     * @return string 替换后的模板内容
     */
    protected function replaceTags(string $content): string
    {
        foreach ($this->templateTags as $tag => $replacement) {
            $pattern = '#' . str_replace('%%', '(.+?)', preg_quote($tag, '#')) . '#imsU';
            $content = preg_replace($pattern, $replacement, $content);
        }

        return $content;
    }

    /**
     * 解析模板块。
     * 如果块不存在，则返回空字符串。
     *
     * @param string $content 模板内容
     * @return string 解析后的模板内容
     */
    protected function parseTemplateBlocks(string $content): string
    {
        $content = preg_replace_callback('/{{\s*block\s+(\w+)\s*}}(.*?){{\s*block_end\s*}}/s', function ($matches) {
            $blockName = $matches[1];
            return isset($this->blocks[$blockName]) ? $this->blocks[$blockName] : '';
        }, $content);

    
        return $content;
    }
    
    
    /**
     * 包含模板内容到一个临时文件中，并通过extract函数将数据数组中的变量导入到作用域中，
     * 然后包括这个临时文件来渲染模板。这允许使用PHP原生语法来渲染模板。
     *
     * @param string $content 模板内容
     * @param array $data 渲染模板时使用的数据
     * @return string 渲染后的模板内容
     */
    protected function includeTemplateContent(string $content, array $data): string
    {
        $tempFileWithoutExtension = tempnam($this->getTempDir(), 'tpl_');
        $tempFile = $tempFileWithoutExtension . '.php';
        file_put_contents($tempFile, $content);
    
        ob_start();
        extract($data, EXTR_SKIP);
        include $tempFile;
        $output = ob_get_clean();
    
        unlink($tempFileWithoutExtension);
        unlink($tempFile);
    
        return trim($output); // 使用 trim 函数消除前后空白
    }
    

    /**
     * 获取临时目录路径。
     * 如果临时目录不存在，则创建它。
     *
     * @return string 临时目录路径
     */
    protected function getTempDir(): string
    {
        $tempDir = $this->templateConfig['snail']['tmp'];

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        return $tempDir;
    }

    /**
     * 记录日志。
     * 如果设置了日志服务，则使用指定的级别记录日志。
     *
     * @param string $message 日志消息
     * @param string $level 日志级别
     */
    protected function log($message, $level = 'info')
    {
        if ($this->logger) {
            $this->logger->log($level, $message);
        }
    }
}