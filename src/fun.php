<?php

/**
 * 助手函数
 */
use Imccc\Snail\Core\Debug;
use Imccc\Snail\Core\UrlBinder;
use Imccc\Snail\Helpers\CurlHelper;

/**
 * 获取路由地址
 * @param $route
 * @param array $params
 * @param bool $absolute
 * @return string
 */
if (!function_exists('url')) {
    function url($route, $params = [], $absolute = true)
    {
        return UrlBinder::getUrl($route, $params, $absolute);
    }
}

/**
 * 跳转页面
 * @param $route
 * @param array $params
 * @param bool $absolute
 * @return void
 */
if (!function_exists('redirect')) {
    function redirect($route, $params = [], $absolute = true)
    {
        header('Location: ' . UrlBinder::getUrl($route, $params, $absolute));
        exit;
    }
}

/**
 * 调试输出
 * @param $data
 * @param bool $style
 * @param bool $die
 * @param bool $echo
 * @return void|string
 */
if (!function_exists('dump')) {
    function dump($data, $style = false, $die = false, $echo = true)
    {
        Debug::dump($data, $style, $die, $echo);
    }
}

/**
 * 发送 HTTP 请求
 * @param $url
 * @param array $opt
 * @return mixed
 */
if (!function_exists('sendRequest')) {
    function sendRequest($url, $opt = [])
    {
        return CurlHelper::sendRequest($url, $opt);
    }
}

