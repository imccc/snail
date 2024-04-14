<?php

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Container;
use RuntimeException;
use SimpleXMLElement;

class ApiService
{
    protected $container;
    protected $format;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->format = $this->getOutputFormat();
    }

    public function show($data): void
    {
        switch ($this->format) {
            case 'json':
                header('Content-Type: application/json');
                echo json_encode($data);
                break;
            case 'xml':
                header('Content-Type: application/xml');
                echo $this->arrayToXml($data);
                break;
            case 'yaml':
                header('Content-Type: application/yaml');
                echo yaml_emit($data);
                break;
            case 'jsonp':
                header('Content-Type: application/javascript');
                $callback = $_GET['callback'] ?? '';
                echo $callback . '(' . json_encode($data) . ');';
                break;
            default:
                $this->handleException('Unsupported output format.');
        }
    }

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

    protected function arrayToXml(array $data, $rootElement = 'root'): string
    {
        $xml = new \SimpleXMLElement('<' . $rootElement . '/>');
        $this->arrayToXmlHelper($data, $xml);
        return $xml->asXML();
    }

    protected function arrayToXmlHelper(array $data, SimpleXMLElement &$xml): void
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
                $xml->addChild("$key", null)->addCData("$value");
            }
        }
    }

    /**
     * 异常处理
     * @param RuntimeException $e
     */
    protected function handleException(RuntimeException $e): void
    {
        ExcteptionHandlerTrait::handleException($e);
    }

}
