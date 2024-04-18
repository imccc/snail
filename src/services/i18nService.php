<?php

namespace Imccc\Snail\Services;

use Exception;
use Imccc\Snail\Core\Container;
use Imccc\Snail\Traits\DebugTrait;
use Imccc\Snail\Traits\HandleExceptionTrait;

class i18nService
{
    protected $translations = [];
    protected $language;
    protected $languagePath;
    protected $conf;
    protected $logger;
    protected $container;
    protected $cacheService;
    protected $cacheconf;

    use HandleExceptionTrait, DebugTrait;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->conf = $container->resolve('ConfigService')->get('language');
        $this->cacheconf = $container->resolve('ConfigService')->get('cache.driver');
        $this->logger = $container->resolve('LoggerService');
        $this->language = $this->conf['language'] ?? 'zh_cn';
        $this->languagePath = $this->conf['languagePath'] ?? __DIR__ . '/translations';
        $this->cacheService = $container->resolve('CacheService');
    }

   
    /**
     * 判断缓存是否文件类型
     */
    public function getCacheType(): bool
    {
        return $this->cacheconf['driver'] !== 'file';
    }

    /**
     * 根据语言配置读取翻译文件
     */
    public function loadTranslations(): void
    {
        $translationFile = "{$this->languagePath}/{$this->language}.php";

        if (!file_exists($translationFile)) {
            self::handleException("Translation file for language '{$this->language}' not found.");
        }

        // 先判断缓存服务是否为文件类型，如果不是文件类型，则使用缓存
        if ($this->getCacheType()) {
            // 否则尝试从缓存中读取翻译
            $cachedTranslations = $this->cacheService->get("translations_{$this->language}");

            if ($cachedTranslations !== null) {
                $this->translations = $cachedTranslations;
                return;
            }

            // 缓存中不存在，从翻译文件中加载
            $translations = include $translationFile;

            // 将翻译缓存起来
            $this->cacheService->set("translations_{$this->language}", $translations);

        } else {
            // 如果不是文件类型，则不使用缓存，直接加载翻译文件
            $translations = include $translationFile;

        }

        // 合并翻译
        $this->translations = array_merge($this->translations, $translations);
    }

    /**
     * 注册翻译
     */
    public function registerTranslation(string $keyword, string $translation): void
    {
        $this->translations[$keyword] = $translation;

        // 如果缓存服务不是文件类型，则更新缓存
        if ($this->getCacheType()) {
            $this->cacheService->set("translations_{$this->language}", $this->translations);
        }
    }

    /**
     * 获取翻译
     */
    public function getTranslation(string $keyword): ?string
    {
        if ($this->conf['trueon']) {
            return $this->translations[$keyword] ?? null;
        } else {
            return $keyword;
        }
    }
}
