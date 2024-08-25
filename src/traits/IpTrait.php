<?php
namespace Imccc\Snail\Traits;
/**
 * IP特性
 *
 * @author  sam <sam@imccc.cc>
 * @since   2024-03-31
 * @version 1.0
 */

trait IpTrait
{
    /**
     * 获取客户端IP
     *
     * @return string|null 客户端IP地址，如果无法获取则返回 null
     */
    public static function ip()
    {
        $ip = null;

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        }
        return $ip;
    }

    /**
     * 随机生成IP
     *
     * @return string 随机生成的IP地址
     */
    public static function makeip()
    {
        $ip1id = rand(1, 255);
        $ip2id = rand(0, 255);
        $ip3id = rand(0, 255);
        $ip4id = rand(0, 255);
        return "$ip1id.$ip2id.$ip3id.$ip4id";
    }

    /**
     * 检查IP是否在白名单中
     *
     * @param string $ip 要检查的IP地址
     * @param array $whitelist 白名单IP地址列表
     * @return bool 是否在白名单中
     */
    public static function allowIp($ip, $whitelist)
    {
        // 如果白名单为空，则默认允许所有IP访问
        if (empty($whitelist)) {
            return true;
        }

        // 检查IP是否在白名单中
        return in_array($ip, $whitelist);
    }

    /**
     * 检查IP是否为禁止访问的IP
     *
     * @param string $ips IP地址列表，逗号分隔的IP列表，或者array数组
     * @return bool 是否为禁止访问的IP
     */
    public static function banip($ips)
    {
        if (is_string($ips)) {
            $ban = array_map('trim', explode(',', $ips));
        } elseif (is_array($ips)) {
            $ban = $ips;
        } else {
            return false;
        }

        // 如果IP列表包含'0.0.0.0'，则禁止所有地址访问
        if (in_array('0.0.0.0', $ban)) {
            return true;
        }

        $ip = self::ip();

        // 如果无法获取客户端IP，则默认不允许访问
        if (!$ip) {
            return true;
        }

        foreach ($ban as $v) {
            if (self::matchIp($v, $ip)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 匹配IP地址是否在列表中
     *
     * @param string $pattern IP地址模式
     * @param string $ip 要匹配的IP地址
     * @return bool 是否匹配成功
     */
    private static function matchIp($pattern, $ip)
    {
        $patternParts = explode('.', $pattern);
        $ipParts = explode('.', $ip);

        if (count($patternParts) !== 4 || count($ipParts) !== 4) {
            return false;
        }

        foreach ($patternParts as $key => $part) {
            if ($part !== '*' && $part !== $ipParts[$key]) {
                return false;
            }
        }
        return true;
    }
}
