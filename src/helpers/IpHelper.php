<?php

/**
 * IP助手类
 *
 * @author  sam <sam@imccc.cc>
 * @since   2024-03-31
 * @version 1.0
 */
namespace Imccc\Snail\Helpers;

use Imccc\Snail\Core\Container;

class IpHelper
{
    protected $container;
    protected $config;

    /**
     * 构造函数
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $this->container->resolve('ConfigService');
    }

    /**
     * 获取客户端IP
     *
     * @return string 客户端IP地址
     */
    public function ip()
    {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
                $ip = getenv("HTTP_X_FORWARDED_FOR");
            } else {
                if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
                    $ip = getenv("REMOTE_ADDR");
                } else {
                    if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
                        $ip = $_SERVER['REMOTE_ADDR'];
                    } else {
                        $ip = "unknown";
                    }
                }
            }
        }
        return $ip;
    }

    /**
     * 随机生成IP
     *
     * @return string 随机生成的IP地址
     */
    public function makeip()
    {
        $ip1id = round(rand(600000, 2550000) / 10000);
        $ip2id = round(rand(600000, 2550000) / 10000);
        $ip3id = round(rand(600000, 2550000) / 10000);
        $ip4id = round(rand(600000, 2550000) / 10000);
        return $ip1id . "." . $ip2id . "." . $ip3id . "." . $ip4id;
    }

    /**
     * 检查IP是否为禁止访问的IP
     *
     * @return bool 是否为禁止访问的IP
     */
    public function banip()
    {
        $ip = $this->ip();
        $ban = $this->config->get("banip.banips");
        if (empty($ban)) {
            return false;
        } else {
            $ips = array_map('trim', explode(',', $ban));
            foreach ($ips as $v) {
                $v = str_replace('.', '\.', $v);
                $v = str_replace('*', '.*', $v);
                if (preg_match("/^{$v}$/", $ip)) {
                    return true;
                }
            }
            return false;
        }
    }
}
