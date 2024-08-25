<?php

/**
 * 字符串处理
 *
 * @author  sam <sam@imccc.cc>
 * @since   2024-03-31
 * @version 1.0
 */
trait StringTrait
{
    /**
     * 驼峰命名法转下划线命名法
     * @param string $str 驼峰命名法字符串
     * @return string 下划线命名法字符串
     * @throws InvalidArgumentException 如果输入参数不是字符串
     */
    public static function camelCaseToUnderScore($str)
    {
        if (!is_string($str)) {
            throw new InvalidArgumentException('Input must be a string');
        }
        // 使用正则表达式将大写字母转为'_'加小写字母，然后转换整个字符串为小写
        return strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $str));
    }

    /**
     * 下划线命名法转驼峰命名法
     * @param string $str 下划线命名法字符串
     * @return string 驼峰命名法字符串
     * @throws InvalidArgumentException 如果输入参数不是字符串
     */
    public static function underScoreToCamelCase($str)
    {
        if (!is_string($str)) {
            throw new InvalidArgumentException('Input must be a string');
        }
        // 使用ucwords将字符串中每个单词的首字母转为大写，移除下划线后，再将第一个单词的首字母转为小写
        return lcfirst(str_replace('_', '', ucwords($str, '_')));
    }
}