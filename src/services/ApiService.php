<?php

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Helpers\SimpleXMLHelper;

class ApiService
{

    protected $container;
    protected $format;

    /**
     * 构造函数，初始化 ApiService
     *
     * @param Container $container 依赖注入容器
     */
    public function __construct(Container $container)
    {
        // 注册全局异常处理函数

        $this->container = $container;
        $this->format = $this->getOutputFormat();
    }

    /**
     * 输出数据
     *
     * @param mixed $data 要输出的数据
     * @return void
     */
    public function show($data): void
    {
        // 设置响应头
        $this->setResponseHeaders();
        $result = [
            'code' => $data['code'] ?? 0,
            'message' => $data['message'] ? $data['msg'] : '',
            'data' => $data['data'] ?? [],
        ];
        $jsuu = $this->getJsuu();
        // 根据输出格式输出数据
        switch ($this->format) {
            case 'json':
                if ($jsuu) {
                    echo json_encode($result, JSON_UNESCAPED_UNICODE);
                } else {
                    echo json_encode($result);
                }
                break;
            case 'xml':
                echo $this->arrayToXml($result);
                break;
            case 'yaml':
                if ($jsuu) {
                    echo yaml_emit($data, YAML_UTF8_ENCODING);
                } else {
                    echo yaml_emit($result);
                }

                break;
            case 'jsonp':
                $callback = $this->getJsonpCallback();
                if ($jsuu) {
                    echo $callback . '(' . json_encode($result, JSON_UNESCAPED_UNICODE) . ');';
                } else {
                    echo $callback . '(' . json_encode($result) . ');';
                }
                break;
            default:
                // 不支持的格式，返回 406 Not Acceptable
                http_response_code(406);
                exit('Not Acceptable');
        }
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
                return 'application/yaml';
            case 'jsonp':
                return 'application/javascript';
            default:
                return '';
        }
    }

    /**
     * 获取字符集
     *
     * @return string 字符集
     */
    protected function getCharset()
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
        return $_SERVER['HTTP_X_JSON_UNESCAPED_UNICODE'] ?? false;
    }

    /**
     * 获取 JSONP 回调函数名
     *
     * @return string 回调函数名
     */
    protected function getJsonpCallback(): string
    {
        $callback = $_GET['callback'] ?? '';
        // 在这里对回调函数名进行验证和清理
        // 这里的示例是简单的清理方式，只允许字母、数字和下划线
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

        // 优先级顺序
        $formats = [
            'application/json' => 'json',
            'application/xml' => 'xml',
            'application/yaml' => 'yaml',
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
     * @param string $rootElement 根元素名称
     * @return string XML 字符串
     */
    protected function xmlhelper($data)
    {
        $xml = new SimpleXMLHelper;
        $encoding = $this->getCharset();
        $xml->xmlEncode($data, 'root', $encoding);
    }
}
