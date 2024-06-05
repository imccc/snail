<?php

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Traits\HandleExceptionTrait;
use Imccc\Snail\Helpers\XmlHelper;

class ApiService
{
    use HandleExceptionTrait;

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
        set_error_handler([self::class, 'handleException']);

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
            'message' => $data['message'] ?? '',
            'data' => $data['data'] ?? [],
        ];

        // 根据输出格式输出数据
        switch ($this->format) {
            case 'json':
                echo json_encode($result);
                break;
            case 'xml':
                echo $this->arrayToXml($result);
                break;
            case 'yaml':
                echo yaml_emit($result);
                break;
            case 'jsonp':
                $callback = $this->getJsonpCallback();
                echo $callback . '(' . json_encode($result) . ');';
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
        header('Content-Type: ' . $this->getContentType());
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
        if (strpos($acceptHeader, 'application/json') !== false) {
            return 'json';
        } elseif (strpos($acceptHeader, 'application/xml') !== false) {
            return 'xml';
        } elseif (strpos($acceptHeader, 'application/yaml') !== false) {
            return 'yaml';
        } elseif (isset($_GET['callback'])) {
            return 'jsonp';
        } else {
            return 'json'; // 默认使用 JSON 格式
        }
    }

    /**
     * 将数组转换为 XML
     *
     * @param array $data 要转换的数组
     * @param string $rootElement 根元素名称
     * @return string XML 字符串
     */
    protected function arrayToXml(array $data, $rootElement = 'root'): string
    {
        $xml = new XmlHelper('<' . $rootElement . '/>');
        $this->arrayToXmlHelper($data, $xml);
        return $xml->asXML();
    }

    /**
     * 递归辅助方法，将数组转换为 XML
     *
     * @param array $data 要转换的数组
     * @param SimpleXMLElement $xml 当前 XML 元素
     * @return void
     */
    protected function arrayToXmlHelper(array $data, XmlHelper &$xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $this->arrayToXmlHelper($value, $xml);
                } else {
                    $subnode = $xml->addChild("$key");
                    $this->arrayToXmlHelper($value, $subnode);
                }
            } else {
                // 使用 CDATA 包装内容
                $this->addChild("$key", null)->addCData("$value");
            }
        }
    }


}
