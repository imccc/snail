<?php
/**
 * 加密助手类
 *
 * @author  sam <sam@imccc.cc>
 * @since   2024-03-31
 * @version 1.0
 */
namespace Imccc\Snail\Helpers;

class EncryptHelper
{
    // 加密方法
    private const METHOD = 'aes-256-cbc';

    /**
     * 加密
     * @param $data
     * @param $key
     * @return string
     */
    public static function encrypt($data, $key)
    {
        $ivLength = openssl_cipher_iv_length(self::METHOD);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($data, self::METHOD, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * 解密
     * @param $data
     * @param $key
     * @return string
     */
    public static function decrypt($data, $key)
    {
        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length(self::METHOD);
        $iv = substr($data, 0, $ivLength);
        $data = substr($data, $ivLength);
        return openssl_decrypt($data, self::METHOD, $key, OPENSSL_RAW_DATA, $iv);
    }

}
