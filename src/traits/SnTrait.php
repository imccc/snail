<?php

trait SnTrait
{
    // 定义常量字符集，提高代码可维护性
    protected static $STR_POL = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";

    /**
     * 生成随机字符串
     * @param int $length 随机字符串的长度，默认为16
     * @return string 生成的随机字符串
     */
    public static function getRandStr($length = 16)
    {
        // 输入验证
        if (!is_int($length) || $length < 1) {
            throw new InvalidArgumentException("长度必须是一个正整数。");
        }

        $str = '';
        $max = strlen(self::$STR_POL) - 1;
        // 使用更安全的 random_int 替代 mt_rand
        for ($i = 0; $i < $length; $i++) {
            $str .= self::$STR_POL[random_int(0, $max)];
        }
        return $str;
    }

    /**
     * 生成UUID V4
     * @return string 生成的UUID
     */
    public static function uuidv4()
    {
        // 使用 random_int 以提高随机性质量
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }
}
