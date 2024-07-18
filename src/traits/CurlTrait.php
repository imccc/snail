<?php

trait CurlTrait
{
    public static function sendRequest($url, $opt = [])
    {
        // 解析请求选项数组
        $params = $opt['params'] ?? [];
        $method = $opt['method'] ?? 'GET';
        $options = $opt['options'] ?? [];
        $fakeIp = $opt['fakeIp'] ?? null;
        $proxy = $opt['proxy'] ?? null;
        $proxyAuth = $opt['proxyAuth'] ?? null; // 新增的代理服务器认证信息

        // 验证 URL 是否为空
        if (empty($url)) {
            return false;
        }

        // 初始化 cURL
        $ch = curl_init();

        // 设置请求 URL
        curl_setopt($ch, CURLOPT_URL, $url);

        // 将响应作为字符串返回，而不是直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 设置请求方法
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($params),
                ));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            // 默认为 GET 请求
            default:
                break;
        }

        // 设置额外的 cURL 选项
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }

        // 如果指定了伪造 IP 地址，则设置 X-Forwarded-For 标头字段
        if (!is_null($fakeIp)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-Forwarded-For: ' . $fakeIp,
            ));
        }

        // 如果指定了代理服务器地址，则设置代理
        if (!is_null($proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);

            // 如果指定了代理服务器认证信息，则设置代理服务器的用户名和密码
            if (!is_null($proxyAuth) && is_array($proxyAuth) && count($proxyAuth) == 2) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, implode(':', $proxyAuth));
            }
        }

        // 执行请求并获取响应内容
        $response = curl_exec($ch);

        // 检查请求是否成功
        if ($response === false) {
            // 请求失败时获取错误信息
            $error = curl_error($ch);
            // 关闭 cURL 资源
            curl_close($ch);
            // 抛出异常或记录日志等错误处理逻辑
                throw new Exception("CURL Error: $error");
        }

        // 关闭 cURL 资源
        curl_close($ch);

        return $response;
    }
}
