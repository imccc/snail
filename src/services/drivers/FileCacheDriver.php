<?php

namespace Imccc\Snail\Services\Engines;

use Imccc\Snail\Core\Container;

class SnailEngine
{
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

    public function render($tpl, $data = [])
    {
        if (!isset($data['tplpath'])) {
            throw new \Exception("Template path (tplpath) not provided in data array.");
        }

        $tplPath = rtrim($data['tplpath'], '/') . '/' . $tpl . $this->templateConfig['snail']['ext'];
        $this->log('Template render: ' . $tplPath, $this->logprefix[2]);

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

        $this->log('Template Render Success: ' . $content, $this->logprefix[0]);

        $output = $this->includeTemplateContent($content, $data);

        return $output;
    }

    protected function parse(string $tplPath, array $data): string
    {
        $content = $this->loadTemplate($tplPath);
        $content = $this->parseTemplateInheritance($content, dirname($tplPath));
        $content = $this->replaceVariables($content, $data);
        $content = $this->replaceTags($content);
        $content = $this->parseTemplateBlocks($content);

        return $content;
    }

    protected function loadTemplate(string $tplPath): string
    {
        if (!file_exists($tplPath)) {
            throw new \Exception("Template file not found: $tplPath");
        }

        return file_get_contents($tplPath);
    }

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

    protected function extractBlocks(string &$content): void
    {
        preg_match_all('/{{\s*block\s+(\w+)\s*}}(.*?){{\s*block_end\s*}}/s', $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $this->blocks[$match[1]] = $match[2];
            $content = str_replace($match[0], '', $content);
        }
    }

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

    protected function replaceTags(string $content): string
    {
        foreach ($this->templateTags as $tag => $replacement) {
            $pattern = '#' . str_replace('%%', '(.+?)', preg_quote($tag, '#')) . '#imsU';
            $content = preg_replace($pattern, $replacement, $content);
        }

        return $content;
    }

    protected function parseTemplateBlocks(string $content): string
    {
        $content = preg_replace_callback('/{{\s*block\s+(\w+)\s*}}(.*?){{\s*block_end\s*}}/s', function ($matches) {
            $blockName = $matches[1];
            return isset($this->blocks[$blockName]) ? $this->blocks[$blockName] : ''; // Return empty string if block not found
        }, $content);

        $this->log('Template Blocks Parsed: ' . print_r($this->blocks, true), $this->logprefix[2]);

        return $content;
    }

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
        unlink($tempFile); // 删除临时文件

        return $output;
    }

    protected function getTempDir(): string
    {
        $tempDir = $this->templateConfig['snail']['tmp'];

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        return $tempDir;
    }

    protected function log($message, $level = 'info')
    {
        if ($this->logger) {
            $this->logger->log($level, $message);
        }
    }
}
