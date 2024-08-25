<?php

namespace Imccc\Snail\Services\Engines;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Services\Service;

class SnailEngine extends Service
{
    protected $config;
    protected $cache;
    protected $loggerPrefix = ['engine', 'debug', 'error'];
    protected $templateConfig;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->cache = $this->container->resolve('CacheService');
        $this->templateConfig = $this->config->get('template');
        $this->templatePath = $this->templateConfig['path'];
        $this->basePath = $this->templateConfig['snail']['base'];
        $this->templateTags = $this->templateConfig['snail']['tags'];
        $this->cacheEnabled = $this->templateConfig['cache'];
    }

    /**
     * 渲染模板
     * @param string $template 模板文件名
     * @param array $data 模板变量
     * @return void
     */
    public function render(string $template, array $data = []): void
    {
        // // 日志当前所有初始值
        // $this->log(array_merge([self::class], [$template], [$data]), $this->loggerPrefix[0]);

        // // 构建模板路径
        // $templateFile = $this->templatePath . DIRECTORY_SEPARATOR . $template;

        // // 检查模板文件是否存在
        // if (!file_exists($templateFile)) {
        //     $this->log("Template file not found: {$templateFile}", $this->loggerPrefix[2]);
        //     throw new \Exception("Template file not found.");
        // }

        // // 获取模板内容
        // $templateContent = file_get_contents($templateFile);

        // // 处理模板继承
        // $templateContent = $this->processInheritance($templateContent);

        // // 构建模板块数组
        // $blocks = $this->extractBlocks($templateContent);

        // // 替换模板块
        // $templateContent = $this->replaceBlocks($templateContent, $blocks);

        // // 渲染模板
        // $renderedContent = $this->renderContent($templateContent, $data);

        // // 压缩输出模板
        // $renderedContent = $this->compressOutput($renderedContent);

        // // 输出结果
        // echo $renderedContent;
    }

    protected function processInheritance(string $content): string
    {
        // 继承处理逻辑
        if (preg_match('/{{\s*extends\s+\'(.+?)\'\s*}}/', $content, $matches)) {
            $parentTemplate = $matches[1];
            $parentTemplatePath = $this->templatePath . DIRECTORY_SEPARATOR . $parentTemplate;

            if (file_exists($parentTemplatePath)) {
                $parentContent = file_get_contents($parentTemplatePath);
                return $this->processInheritance($parentContent) . "\n" . $content;
            } else {
                $this->log("Parent template file not found: {$parentTemplatePath}", $this->loggerPrefix[2]);
                throw new \Exception("Parent template file not found.");
            }
        }
        return $content;
    }

    protected function extractBlocks(string $content): array
    {
        // 提取模板块逻辑
        $blocks = [];
        if (preg_match_all('/{{\s*block\s+\'(.*?)\'\s*}}(.*?)(?:{{\s*endblock\s*}})/s', $content, $matches)) {
            foreach ($matches[1] as $index => $blockName) {
                $blocks[$blockName] = $matches[2][$index];
            }
        }
        return $blocks;
    }

    protected function replaceBlocks(string $content, array $blocks): string
    {
        foreach ($blocks as $blockName => $blockContent) {
            $content = preg_replace('/{{\s*block\s+\'' . preg_quote($blockName, '/') . '\'s*}}.*?{{\s*endblock\s*}}/s', $blockContent, $content);
        }
        return $content;
    }

    protected function compressOutput(string $content): string
    {
        return preg_replace('/\s+/', ' ', $content);
    }

}
