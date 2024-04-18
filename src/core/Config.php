<?php
/**
 * 配置类
 *
 * @package Imccc\Snail
 * @since 0.0.1
 * @author Imccc
 * @copyright Copyright (c) 2024 Imccc.
 */
namespace Imccc\Snail\Core;

class Config
{
    /**
     * 加载配置文件 支持多配置。支持覆盖系统配置，优先载入app目录下的配置，不存在则载入框架默认配置
     * @access public
     * @param  string $configfile  配置参数名
     * @return mixed
     */
    public static function load($configfile)
    {
        $cf = CONFIG_PATH . DIRECTORY_SEPARATOR . $configfile . CFG_EXT;
        $acf = APP_CONFIG_PATH . DIRECTORY_SEPARATOR . $configfile . CFG_EXT;
        if (file_exists($acf)) {
            return include $acf;
        } elseif (file_exists($cf)) {
            return include $cf;
        } else {
            return [];
        }
    }

    /**
     * 获取配置参数 为空则获取所有配置
     * @access public
     * @param  string $key    配置参数名（支持多级配置 {CS}号分割）
     * @param  mixed  $def    默认值
     * @return mixed
     */
    public static function get($key = "", $def = "")
    {
        if (!$key) {
            return false;
        }
        $pm = explode(CS, $key);
        $f = $pm[0];
        $cfg = self::load($f);
        //没有{CS}分割符直接返回全部
        if (false === strpos($key, CS)) {
            return $cfg;
        } else {
            foreach ($pm as $val) {
                if ($f == $val) {
                    unset($pm[$val]); //移除文件名
                } else {
                    if (isset($cfg[$val])) {
                        $cfg = $cfg[$val];
                    } else {
                        return $def;
                    }
                }
            }
            return $cfg;
        }
    }

    /**
     * 设置配置参数
     * @access public
     * @param  string $key    配置参数名（支持多级配置 {CS}号分割）
     * @param  mixed  $value  配置值
     * @return array          设置后的完整配置数组
     */
    public static function set(string $key, mixed $value): array
    {
        if (!$key) {
            return [];
        }
        $pm = explode(CS, $key);
        $f = $pm[0];
        $cfg = self::load($f);
        $current = &$cfg;

        foreach ($pm as $val) {
            if ($f == $val) {
                unset($pm[$val]); //移除文件名
            } else {
                if (!isset($current[$val]) || !is_array($current[$val])) {
                    $current[$val] = [];
                }
                $current = &$current[$val];
            }
        }

        $current = $value;

        return $cfg;
    }

    /**
     * 保存设置
     * @access public
     * @param  string $configfile  配置参数名
     * @param  mixed  $val         配置值
     * @return mixed
     */
    public static function save($configfile, $val)
    {
        $cfg = self::load($configfile);
        $cfg = array_merge($cfg, $val);
        $acf = APP_CONFIG_PATH . DIRECTORY_SEPARATOR . $configfile . CFG_EXT;
        file_put_contents($acf, "<?php \n return " . var_export($cfg, true) . ";");
    }
}
