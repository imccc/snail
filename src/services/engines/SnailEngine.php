<?php

namespace Imccc\Snail\Services\Engines;

use Imccc\Snail\Core\Container;
use RuntimeException;

class SnailEngine
{
    protected $container;
    protected $config;
    protected $logger;
    protected $cache;
    protected $templateConfig;
    protected $templatePath;
    protected $templateTags;
    protected $blocks = [];
    protected $currentBlock;
    protected $blockStack = [];
    protected $cacheEnabled;
    protected $functions = [];
    protected $parentTemplate;
    protected $basePath;
    protected $loggerprefix = ['engine', 'debug', 'error'];

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $this->container->resolve('ConfigService');
        $this->cache = $this->container->resolve('CacheService');
        $this->logger = $this->container->resolve('LoggerService');
        $this->templateConfig = $this->config->get('template');
        $this->templatePath = $this->templateConfig['path'];
        $this->basePath = $this->templateConfig['snail']['base'];
        $this->templateTags = $this->templateConfig['snail']['tags'];
        $this->cacheEnabled = $this->templateConfig['cache'];
    }

    public function render($template, $data = [])
    {
        $this->logger->log('Rendering template: ' . $template, $this->loggerprefix[0]);

        $templatePath = $this->getTemplatePath($template);

        if ($this->cacheEnabled && $cachedContent = $this->cache->get($templatePath)) {
            $this->logger->log('Using cached content for template: ' . $templatePath, $this->loggerprefix[0]);
            $compiledTemplate = $cachedContent;
        } else {
            $this->logger->log('Compiling template: ' . $templatePath, $this->loggerprefix[0]);
            $compiledTemplate = $this->compile(file_get_contents($templatePath));
            if ($this->cacheEnabled) {
                $this->cache->set($templatePath, $compiledTemplate);
            }
        }

        if ($this->parentTemplate) {
            $this->logger->log('Parent template found: ' . $this->parentTemplate, $this->loggerprefix[0]);

            $parentTemplatePath = $this->getTemplatePath($this->parentTemplate);
            $parentContent = file_get_contents($parentTemplatePath);
            $compiledParent = $this->compile($parentContent);
            $compiledTemplate = $this->mergeTemplates($compiledParent, $compiledTemplate);
        }

        $compiledTemplate = $this->replaceStaticResources($compiledTemplate);
        $compiledTemplate = $this->removeBlockTags($compiledTemplate);

        $this->logger->log('Compiled Template: ' . $compiledTemplate, $this->loggerprefix[0]);
        $tempFile = $this->saveToTempFile($compiledTemplate);

        extract($data);
        $engine = $this;

        include $tempFile;
        unlink($tempFile);
    }

    protected function compile($content)
    {
        $this->logger->log('Compiling Template: ' . $content, $this->loggerprefix[0]);
        $compileContent = $this->parseTags($content);
        $this->logger->log('Compiled Template: ' . $compileContent, $this->loggerprefix[0]);
        return $compileContent;
    }

    protected function parseTags($content)
    {
        $tags = $this->templateTags;
        foreach ($tags as $tag => $replacement) {
            $pattern = $this->createPattern($tag);
            $content = preg_replace_callback($pattern, function ($matches) use ($replacement, $tag) {
                if (is_string($replacement)) {
                    return $this->replaceCallback($matches, $replacement);
                } elseif ($replacement instanceof \Closure) {
                    return call_user_func($replacement, $matches);
                } else {
                    throw new \RuntimeException("Replacement must be a string or a Closure. Given type: " . gettype($replacement));
                }
            }, $content);
        }

        $content = preg_replace_callback('/\{\{\s*(\$[a-zA-Z_][a-zA-Z0-9_]*)\s*\}\}/', function ($matches) {
            return '<?php echo ' . $matches[1] . '; ?>';
        }, $content);

        $content = preg_replace_callback('/\{\{\s*([a-zA-Z_][a-zA-Z0-9_]*\([^\}]*)\s*\}\}/', function ($matches) {
            return '<?php echo ' . $matches[1] . '; ?>';
        }, $content);

        $content = $this->processBlocks($content);

        $content = preg_replace_callback('/{{\s*extend\s+"([^"]+)"\s*}}/', function ($matches) {
            $this->parentTemplate = $matches[1];
            return '';
        }, $content);

        return $content;
    }

    protected function createPattern($tag)
    {
        $pattern = preg_quote($tag, '/');
        $pattern = str_replace(['%%'], ['(.+?)'], $pattern);
        return '/' . $pattern . '/';
    }

    protected function replaceCallback($matches, $replacement)
    {
        if (!is_string($replacement)) {
            throw new \RuntimeException("Replacement must be a string. Given type: " . gettype($replacement));
        }

        for ($i = 1; $i < count($matches); $i++) {
            $replacement = str_replace('\\' . $i, $matches[$i], $replacement);
        }
        return $replacement;
    }

    protected function getTemplatePath($template)
    {
        if (strpos($template, '/') !== false) {
            return $template . $this->templateConfig['snail']['ext'];
        } else {
            return $this->basePath . $template;
        }
    }

    protected function saveToTempFile($content)
    {
        $tempDir = $this->templateConfig['snail']['tmp'];
        if (!is_dir($tempDir)) {
            if (!mkdir($tempDir, 0755, true)) {
                throw new RuntimeException("Failed to create temporary directory '$tempDir'.");
            }
        }

        $tempFile = tempnam($tempDir, 'snail_');
        if (!$tempFile || !file_put_contents($tempFile, $content)) {
            throw new RuntimeException("Failed to save temporary file '$tempFile'.");
        }

        return $tempFile;
    }

    protected function processBlocks($content)
    {
        $pattern = '/{{\s*block\s+("?\'?)(\w+)\1\s*}}(.*?){{\s*\/block\s*}}/s';
        $blocks = [];

        $content = preg_replace_callback($pattern, function ($matches) use (&$blocks) {
            $blockName = $matches[2];
            $blockContent = $matches[3];
            $blocks[$blockName] = $blockContent;
            return '[[BLOCK_' . $blockName . '_START]]' . '[[BLOCK_END]]';
        }, $content);

        $this->blocks = array_merge($this->blocks, $blocks);

        return $content;
    }

    protected function mergeTemplates($parentContent, $childContent)
    {
        // 提取父模板和子模板中的块
        $parentBlocks = $this->extractBlocks($parentContent);
        $childBlocks = $this->extractBlocks($childContent);

        // 合并子模板块到父模板块
        $mergedBlocks = array_merge($parentBlocks, $childBlocks);

        // 替换父模板中的块标签，使用子模板中的块内容
        foreach ($parentBlocks as $blockName => $blockContent) {
            $pattern = '/\[\[BLOCK_' . preg_quote($blockName, '/') . '_START\]\](.*?)\[\[BLOCK_END\]\]/s';
            if (isset($childBlocks[$blockName])) {
                // 替换为子模板块内容
                $parentContent = preg_replace($pattern, $childBlocks[$blockName], $parentContent);
            } else {
                // 保持父模板块内容（未被子模板覆盖）
                $parentContent = preg_replace($pattern, $blockContent, $parentContent);
            }
        }

        // 查找 <body> 标签位置
        $bodyPos = strpos($parentContent, '</body>');

        // 将子模板中未在父模板中定义的块内容追加到 <body> 标签前
        $additionalBlocks = array_diff_key($childBlocks, $parentBlocks);
        $additionalContent = implode("\n", $additionalBlocks);
        $parentContent = substr_replace($parentContent, $additionalContent, $bodyPos, 0);

        return $parentContent;
    }

    protected function extractBlocks($content)
    {
        $blocks = [];
        $pattern = '/\[\[BLOCK_(\w+)_START\]\](.*?)\[\[BLOCK_END\]\]/s';

        preg_replace_callback($pattern, function ($matches) use (&$blocks) {
            $blockName = $matches[1];
            $blockContent = $matches[2];
            $blocks[$blockName] = $blockContent;
        }, $content);

        return $blocks;
    }

    protected function replaceStaticResources($content)
    {
        if (isset($this->templateConfig['library']) && isset($this->templateConfig['static'])) {
            $library = $this->templateConfig['library'];
            $staticPath = $this->templateConfig['static'];

            foreach ($library as $key => $path) {
                $content = str_replace($key, $staticPath . $path, $content);
            }
        } else {
            $this->logger->log('Static or library paths not defined in configuration.', $this->loggerprefix[0]);
        }

        return $content;
    }

    protected function removeBlockTags($content)
    {
        // Patterns to match BLOCK tags and block tags with or without quotes
        $pattern = '/\[\[BLOCK_(\w+)_START\]\](.*?)\[\[BLOCK_END\]\]/s';
        $patternWithQuotes = '/\{\{\s*block\s+(["\']?)(\w+)\1\s*\}\}(.*?)\{\{\s*\/block\s*\}\}/s';

        // Handle blocks with BLOCK tags first
        $content = preg_replace_callback($pattern, function ($matches) {
            $blockName = $matches[1];
            $blockContent = isset($this->blocks[$blockName]) ? $this->blocks[$blockName] : $matches[2];
            return $blockContent;
        }, $content);

        // Handle blocks with block tags (with or without quotes)
        $content = preg_replace_callback($patternWithQuotes, function ($matches) {
            $blockName = $matches[2];
            $blockContent = isset($this->blocks[$blockName]) ? $this->blocks[$blockName] : $matches[3];
            return $blockContent;
        }, $content);

        return $content;
    }
}
