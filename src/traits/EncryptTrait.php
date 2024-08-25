<?php
/**
 * 加解密
 *
 * @author  sam <sam@imccc.cc>
 * @since   2024-03-31
 * @version 1.0
 *
 * 在特性中，使用了`aes-256-cbc`作为加密方法。除此之外，OpenSSL 还支持许多其他的加密方法，你可以根据需求选择合适的加密算法。以下是一些常用的 OpenSSL 加密方法：
 * 1. **AES（高级加密标准）**：
 * - `aes-128-cbc`
 * - `aes-192-cbc`
 * - `aes-256-cbc`
 * - `aes-128-ecb`
 * - `aes-192-ecb`
 * - `aes-256-ecb`

 * 2. **DES（数据加密标准）**：
 * - `des-cbc`
 * - `des-ede3` (3DES)

 * 3. **RC4（Rivest Cipher 4）**：
 * - `rc4`

 * 4. **Blowfish**：
 * - `bf-cbc`

 * 5. **RC2**：
 * - `rc2-cbc`

 * 6. **CAST-128**：
 * - `cast5-cbc`

 * 7. **IDEA**：
 * - `idea-cbc`

 * 8. **SEED**：
 * - `seed-cbc`

 * 9. **Camellia**：
 * - `camellia-128-cbc`
 * - `camellia-192-cbc`
 * - `camellia-256-cbc`

 * 10. **SM4**：
 * - `sm4-cbc`

 * 这只是一小部分 OpenSSL 支持的加密方法。你可以根据你的安全需求和性能要求选择合适的加密方法。
 */

trait EncryptTrait
{
    // 加密方法
    protected static $METHOD = 'aes-256-cbc';

    /**
     * 加密
     * @param string $data 要加密的数据
     * @param string $key 加密密钥
     * @return string 加密后的数据
     */
    public static function encrypt($data, $key)
    {
        $ivLength = openssl_cipher_iv_length(self::$METHOD);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($data, self::$METHOD, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * 解密
     * @param string $data 要解密的数据
     * @param string $key 解密密钥
     * @return string 解密后的数据
     */
    public static function decrypt($data, $key)
    {
        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length(self::$METHOD);
        $iv = substr($data, 0, $ivLength);
        $data = substr($data, $ivLength);
        return openssl_decrypt($data, self::$METHOD, $key, OPENSSL_RAW_DATA, $iv);
    }
}
