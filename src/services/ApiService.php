<?php

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Helpers\SimpleXMLHelper;

class ApiService extends Service
{
    protected $container;
    protected $config;
    protected $format;

    /**
     * 构造函数，初始化 ApiService
     *
     * @param Container $container 依赖注入容器
     */
 /**
     * 构造函数
     *
     * @param Container $container 服务容器
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->config = $this->container->resolve('ConfigService')->get('api');
        if ($this->config['outformat']) {
            $this->format = $this->config['outformat'];
        } else {
            $this->format = $this->getOutputFormat();
        }
    }

    /**
     * 处理数据
     *
     * @param mixed $data 要输出的数据
     * @return string
     */
    public function format($data): string
    {
        $this->setResponseHeaders();
        $result = $this->prepareResult($data);
        return $this->generateOutput($result);
    }

    /**
     * 显示API数据
     *
     * @param mixed $data 要显示的数据
     * @return void
     */
    public function show($data): void
    {
        echo $this->format($data);
    }

    /**
     * 设置响应头
     *
     * @return void
     */
    protected function setResponseHeaders(): void
    {
        header('Content-Type: ' . $this->getContentType() . '; charset=' . $this->getCharset());
    }

    /**
     * 根据输出格式获取 Content-Type
     *
     * @return string Content-Type
     */
    protected function getContentType(): string
    {
        switch ($this->format) {
            case 'json':
                return 'application/json';
            case 'xml':
                return 'application/xml';
            case 'yaml':
                return 'application/x-yaml';
            case 'jsonp':
                return 'application/javascript';
            default:
                return 'text/plain';
        }
    }

    /**
     * 获取字符集
     *
     * @return string 字符集
     */
    protected function getCharset(): string
    {
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (preg_match('/charset=([^;]+)/', $acceptHeader, $matches)) {
            return $matches[1];
        } else {
            return 'utf-8';
        }
    }

    /**
     * 获取 JSON 是否需要转义
     *
     * @return bool
     */
    protected function getJsuu(): bool
    {
        return (bool) ($_SERVER['HTTP_X_JSON_UNESCAPED_UNICODE'] ?? false);
    }

    /**
     * 获取 JSONP 回调函数名
     *
     * @return string 回调函数名
     */
    protected function getJsonpCallback(): string
    {
        $callback = $_GET['callback'] ?? '';
        return preg_replace('/[^a-zA-Z0-9_]/', '', $callback);
    }

    /**
     * 根据请求头 Accept 获取输出格式
     *
     * @return string 输出格式
     */
    protected function getOutputFormat(): string
    {
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';

        if (isset($_GET['callback'])) {
            return 'jsonp';
        }

        $formats = [
            'application/json' => 'json',
            'application/xml' => 'xml',
            'application/yaml' => 'yaml',
            'application/x-yaml' => 'yaml',
            'application/javascript' => 'jsonp',
            'text/html' => 'html',
            'text/plain' => 'text',
        ];

        foreach ($formats as $mime => $format) {
            if (strpos($acceptHeader, $mime) !== false) {
                return $format;
            }
        }

        return 'json'; // 默认使用 JSON 格式
    }

    /**
     * 将数组转换为 XML
     *
     * @param array $data 要转换的数组
     * @return string XML 字符串
     */
    protected function xmlhelper(array $data): string
    {
        $encoding = 'utf-8';
        $xmlHelper = new SimpleXMLHelper('<root></root>');
        return $xmlHelper->xmlEncode($data, 'root', $encoding);
    }

    /**
     * 响应数据
     *
     * @param array $data 要输出的数据
     * @param int $code 状态码
     * @param string $message 状态信息
     * @return array 响应数据
     */
    public function result($data = [], $code = 200, $message = 'success')
    {
        return [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * 准备响应结果
     *
     * @param mixed $data
     * @return array
     */
    protected function prepareResult($data): array
    {
        if (is_array($data) && isset($data['code'], $data['message'], $data['data'])) {
            return $data;
        }

        if (is_string($data)) {
            return $this->result([], $data);
        }

        if (is_integer($data)) {
            return $this->result($data);
        }

        $code = $data['code'] ?? 200;
        $message = $data['message'] ?? 'Success';
        $data = $data['data'] ?? $data;
        return $this->result($data, $code, $message);
    }

    /**
     * 生成输出内容
     *
     * @param array $result
     * @return string
     */
    protected function generateOutput(array $result): string
    {
        $jsuu = $this->getJsuu();
        $output = null;

        switch ($this->format) {
            case 'json':
                $output = json_encode($result, $jsuu ? JSON_UNESCAPED_UNICODE : 0);
                break;
            case 'xml':
                $output = $this->xmlhelper($result);
                break;
            case 'yaml':
                $output = yaml_emit($result, $jsuu ? YAML_UTF8_ENCODING : 0);
                break;
            case 'jsonp':
                $callback = $this->getJsonpCallback();
                $output = $callback . '(' . json_encode($result, $jsuu ? JSON_UNESCAPED_UNICODE : 0) . ');';
                break;
            default:
                http_response_code(406);
                exit('Not Acceptable');
        }

        return $output;
    }
}
