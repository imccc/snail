<?php
namespace Imccc\Snail\Services\Engines;

use Imccc\Snail\Core\Container;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TwigEngine
{
    protected $twig;
    protected $container;
    protected $logprefix = ['template', 'error','debug'];
    protected $config;
    protected $logger;
    protected $templateConfig;
    protected $pubbase;
    protected $urlService;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $container->resolve('ConfigService');
        $this->logger = $container->resolve('LoggerService');
        $this->templateConfig = $this->config->get('template');
        $this->urlService = $this->container->resolve('UrlService');
        $this->pubbase = $this->templateConfig['twig']['base'];

        // 初始化 Twig 环境
        $loader = new FilesystemLoader(); // 初始化空的 Twig 加载器
        $twigConfig = $this->templateConfig['twig']['options'] ?? [];
        $this->twig = new Environment($loader, $twigConfig);

        // 注册自定义函数
        $this->registerUrlFunction();
        $this->registerStaticFunction();
    }

    protected function registerUrlFunction()
    {
        $urlFunction = new TwigFunction('url', function ($route, $params = [], $suffix = '', $domain = false, $method = 'GET') {
            return $this->urlService->url($route, $params, $suffix, $domain, $method);
        });
        $this->twig->addFunction($urlFunction);
    }

    protected function registerStaticFunction()
    {
        $staticFunction = new TwigFunction('static', function ($resource) {
            $staticPath = rtrim($this->templateConfig['static'], '/') . '/';
            $library = $this->templateConfig['library'];
            return isset($library[$resource]) ? $staticPath . $library[$resource] : $resource;
        });
        $this->twig->addFunction($staticFunction);
    }

    public function render(string $tpl, array $data = []): string
    {
        $tpl .= $this->templateConfig['twig']['ext'];
        try {
            // 获取模板目录和文件名
            $templateDir = dirname($tpl);
            $templateFile = basename($tpl);
            $this->logger->log(self::class ." : [". __FUNCTION__ . '] : Template file: ' . $tpl, $this->logprefix[0]);

            // 动态设置 Twig 加载路径
            $this->twig->getLoader()->addPath($templateDir);
            $this->twig->getLoader()->addPath($this->pubbase);

            // 渲染模板
            return $this->twig->render($templateFile, $data);
        } catch (\Exception $e) {
            throw new \Exception("Template file not found or rendering error: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
