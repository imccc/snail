<?php
namespace Imccc\Snail\Helpers;

/**
 * 时间类
 */
class TimesHelper
{
    /**
     * 获取当前时间戳
     * @return int
     */
    public static function getTime()
    {
        return time();
    }

    /**
     * 获取当前毫秒时间戳
     * @return int
     */
    public static function getMicroTime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return (float) sprintf('%.0f', (floatval($usec) + floatval($sec)) * 1000);
    }

    /**
     * 获取当前毫秒时间戳
     * @return int
     */
    public static function getMicroTime2()
    {
        list($usec, $sec) = explode(" ", microtime());
        return (float) sprintf('%.0f', (floatval($usec) + floatval($sec)) * 1000000);
    }

    /**
     * 格式化时间戳
     * @param $time
     * @param string $format
     * @return false|string
     */
    public static function formatTime($time, $format = 'Y-m-d H:i:s')
    {
        return date($format, $time);
    }

    /**
     * 比较时间戳
     * @param $time1
     * @param $time2
     * @return int
     */
    public static function compareTime($time1, $time2)
    {
        if ($time1 == $time2) {
            return 0;
        }
        return $time1 > $time2 ? 1 : -1;
    }

    /**
     * 计算时间差
     * @param $time1
     * @param $time2
     * @return string
     */
    public static function timeDiff($time1, $time2)
    {
        $time1 = strtotime($time1);
        $time2 = strtotime($time2);
        $diff = $time1 - $time2;
        $day = floor($diff / 86400);
        $hour = floor(($diff - $day * 86400) / 3600);

        if ($day > 0) {
            return $day . '天' . $hour . '小时';
        } else {
            return $hour . '小时';
        }
    }

}
