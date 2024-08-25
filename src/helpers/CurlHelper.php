<?php
namespace Imccc\Snail\Helpers;

class CurlHelper
{
    public static function sendRequest($url, $opt = [])
    {
        // 合并默认选项和用户传入选项
        $defaultOpt = [
            'params' => [],
            'method' => 'GET',
            'options' => [],
            'fakeIp' => null,
            'proxy' => null,
            'proxyAuth' => null,
            'cookies' => null,
            'cookieFile' => null,
            'sessionFile' => null,
        ];
        $options = array_merge($defaultOpt, $opt);

        // 验证 URL 是否为空
        if (empty($url)) {
            throw new \InvalidArgumentException("URL cannot be empty");
        }

        // 初始化 cURL
        $ch = curl_init();

        // 设置请求 URL
        if ($options['method'] === 'GET' && !empty($options['params'])) {
            $url .= '?' . http_build_query($options['params']);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 初始化 HTTP 头部信息数组
        $headers = [];

        // 设置请求方法
        switch ($options['method']) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options['params']));
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                $jsonData = json_encode($options['params']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Content-Length: ' . strlen($jsonData);
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default:
                break;
        }

        // 设置额外的 cURL 选项
        if (!empty($options['options'])) {
            curl_setopt_array($ch, $options['options']);
        }

        // 设置伪造 IP 地址
        if (!is_null($options['fakeIp'])) {
            $headers[] = 'X-Forwarded-For: ' . $options['fakeIp'];
        }

        // 设置代理
        if (!is_null($options['proxy'])) {
            curl_setopt($ch, CURLOPT_PROXY, $options['proxy']);
            if (!is_null($options['proxyAuth']) && is_array($options['proxyAuth']) && count($options['proxyAuth']) == 2) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, implode(':', $options['proxyAuth']));
            }
        }

        // 设置 Cookie
        if (!is_null($options['cookies'])) {
            curl_setopt($ch, CURLOPT_COOKIE, http_build_query($options['cookies'], '', '; '));
        }

        // 设置 Cookie 文件和会话文件
        $cookieFile = $options['cookieFile'] ?? $options['sessionFile'];
        if (!is_null($cookieFile)) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        }

        // 统一设置 HTTP 头部信息
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // 执行请求并获取响应内容
        $response = curl_exec($ch);

        // 检查请求是否成功
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException("CURL Error: $error. URL: $url. Method: " . strtoupper($options['method']));
        }

        // 关闭 cURL 资源
        curl_close($ch);

        return $response;
    }
}
