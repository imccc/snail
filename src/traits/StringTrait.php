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
     */
    public static function camelCaseToUnderScore($str)
    {
        return strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $str));
    }

    /**
     * 下划线命名法转驼峰命名法
     * @param string $str 下划线命名法字符串
     * @return string 驼峰命名法字符串
     */
    public static function underScoreToCamelCase($str)
    {
        return lcfirst(str_replace('_', '', ucwords($str, '_')));
    }
}
