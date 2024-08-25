<?php
/**
 * 配置类
 *
 * @package Imccc\Snail
 * @since 0.0.1
 * @author Imccc
 * @copyright Copyright (c) 2024 Imccc.
 */

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Services\Service;

class ConfigService
{
    protected $container;
    protected $logger;
    protected $prefix = ['config','debug','error'];
    /**
     * 构造函数
     *
     * @param Container $container 服务容器
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->logger = $container->resolve('LoggerService');
    }

    /**
     * 加载配置文件 支持多配置。支持覆盖系统配置，优先载入app目录下的配置，不存在则载入框架默认配置
     * @access public
     * @param  string $configfile  配置参数名
     * @return mixed
     */
    public function load($configfile)
    {
        // 获取配置文件路径
        $cf = CONFIG_PATH . DIRECTORY_SEPARATOR . $configfile . CFG_EXT;
        $acf = APP_CONFIG_PATH . DIRECTORY_SEPARATOR . $configfile . CFG_EXT;
        // 检查app目录下的配置文件是否存在，存在则返回配置信息，否则返回框架默认配置
        if (file_exists($acf)) {
            $this->log("Loading config file: " . $acf,$this->prefix[0]);
            return include $acf;
        } elseif (file_exists($cf)) {
            $this->log("Loading config file: " . $cf,$this->prefix[0]);
            return include $cf;
        } else {
            $this->log("Config file not found: " . $cf,$this->prefix[2]);
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
    public function get($key = "", $def = "")
    {
        if (!$key) {
            return false;
        }
        $this->log("Getting Config Key: " . $key, $this->prefix[0]);
        $pm = explode(CS, $key);
        $f = $pm[0];
        $cfg = $this->load($f);
        // 没有{CS}分割符直接返回全部配置
        if (false === strpos($key, CS)) {
            $this->log("Getting Config Value: " . $cfg, $this->prefix[0]);
            return $cfg;
        } else {
            foreach ($pm as $val) {
                if ($f == $val) {
                    unset($pm[$val]); // 移除文件名
                } else {
                    if (isset($cfg[$val])) {
                        $cfg = $cfg[$val];
                    } else {
                        return $def;
                    }
                }
            }
            $this->log("Getting Config Value: " . $cfg, $this->prefix[0]);
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
    public function set(string $key, mixed $value): array
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
    public function save($configfile, $val)
    {
        // 获取配置文件路径
        $cfg = $this->load($configfile);
        $cfg = array_merge($cfg, $val);
        $acf = APP_CONFIG_PATH . DIRECTORY_SEPARATOR . $configfile . CFG_EXT;
        $this->log("Saving config file: " . $acf, $this->prefix[0]);
        // 写入配置文件
        file_put_contents($acf, "<?php \n return " . var_export($cfg, true) . ";");
    }

    public function log($msg, $prefix = 'debug')
    {
        $this->logger->log($msg, $prefix);
    }
   

}
