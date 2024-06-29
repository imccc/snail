<?php

namespace Imccc\Snail\Services\Engines;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Traits\DebugTrait;

class SnailEngine
{
    use DebugTrait;

    protected $container;
    protected $config;
    protected $cache;
    protected $logger;
    protected $logprefix = ['template', 'error', 'debug'];
    protected $templateConfig;
    protected $blocks = [];

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
     * 渲染模板
     *
     * @param string $tpl 模板文件路径
     * @param array $data 渲染模板时所需的数据
     * @return string 渲染后的模板内容
     */
    public function render($tpl, $data = [])
    {
        $tplPath = $tpl.$this->templateConfig['snail']['ext'];
        $this->logger->log(self::class . 'Template render: ' . $tplPath, $this->logprefix[2]);

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
        $this->logger->log('Snail Template Render Success: ' . $content, $this->logprefix[0]);

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

        // 解析模板继承和块
        $content = $this->parseTemplateInheritance($content);

        // 替换模板变量
        $content = $this->replaceVariables($content, $data);

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
        try {
            $content = file_get_contents($tplPath);
        } catch (\RuntimeException $e) {
            $content = '';
            throw new Exception("Template file not found: $tplPath");
        }
        return $content;
    }

    /**
     * 解析模板继承和块
     *
     * @param string $content 待解析的模板内容
     * @return string 解析后的模板内容
     */
    protected function parseTemplateInheritance(string $content): string
    {
        // 解析继承标签
        $content = preg_replace_callback('/{%\s*extends\s+"([^"]+)"\s*%}/', function ($matches) {
            $parentTemplate = $this->loadTemplate($matches[1]);
            return str_replace('{__CONTENT__}', $content, $parentTemplate);
        }, $content);

        // 解析块标签
        $content = preg_replace_callback('/{%\s*block\s+(\w+)\s*%}(.*?)\{%\s*endblock\s*%}/s', function ($matches) {
            $this->blocks[$matches[1]] = $matches[2];
            return '';
        }, $content);

        return $this->parseTemplateBlocks($content);
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
            $content = str_replace("{{$key}}", htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $content);
        }

        return $content;
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

        return preg_replace_callback('/{%\s*block\s+(\w+)\s*%}/', function ($matches) {
            $blockName = $matches[1];
            return isset($this->blocks[$blockName]) ? $this->blocks[$blockName] : '';
        }, $content);
    }

    /**
     * 获取供应商静态资源路径
     *
     * @param string $vendor 供应商名称
     * @param string $assetType 资源类型
     * @return string 静态资源路径
     */
    protected function getVendorAssetPath(string $vendor, string $assetType): string
    {
        // 根据供应商和资源类型获取静态资源路径的实现
        // 这里可以根据实际需求进行具体实现
        return "/assets/{$vendor}/{$assetType}.min.js"; // 示例实现
    }
}
