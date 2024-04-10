<?php
namespace Imccc\Snail\Helper;

/**
 * 字符串处理助手类
 *
 * @author  sam <sam@imccc.cc>
 * @since   2024-03-31
 * @version 1.0
 */
class StringHelper
{
    /**
     * 驼峰命名法转下划线命名法
     * @param $str
     * @return string
     */
    public static function camelCase($str)
    {
        return preg_replace_callback('/_([a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, $str);
    }

    /**
     * 下划线命名法转驼峰命名法
     * @param $str
     * @return string
     */
    public static function underscore($str)
    {
        return strtolower(preg_replace('/([A-Z])/', '_', lcfirst($str)));
    }

    /**
     * 金额转换成中文
     * @param $number
     * @return string
     */
    public static function numberTransCny($number)
    {
        // 去除逗号
        $number = str_replace(",", "", $number);
        $number = str_replace("-", "", $number);
        // 转换成数字
        $number = floatval($number);

        $numbersUp = ["零", "壹", "贰", "叁", "肆", "伍", "陆", "柒", "捌", "玖"];
        $units = ["", "拾", "佰", "仟"];
        $bigUnits = ["", "万", "亿", "万亿"];
        $sum = explode(".", $number);
        $integer = $sum[0]; // 取得整数部分
        $fraction = isset($sum[1]) ? rtrim($sum[1], '0') : ''; // 取得小数部分，去除末尾多余的零

        $output = "";

        // 如果整数部分和小数部分均为0，则直接返回“零元整”
        if ($integer == 0 && $fraction == 0) {
            return "零元整";
        }

        // 处理整数部分
        if ($integer > 0) {
            $integerParts = strrev((string) $integer);
            $groups = str_split($integerParts, 4); // 每四位分为一组
            foreach ($groups as $i => $group) {
                $groupOutput = "";
                $digits = str_split($group);
                $zeroFlag = false; // 标记是否需要插入零
                foreach ($digits as $j => $digit) {
                    $unit = $units[$j % 4];
                    $numUp = $numbersUp[$digit];
                    if ($digit != "0") {
                        $groupOutput = $numUp . $unit . $groupOutput;
                        $zeroFlag = true;
                    } else if ($digit == "0" && $zeroFlag) {
                        if ($unit != "" || !$groupOutput) { // 当不是个位或者组输出为空时不处理零
                            $groupOutput = "零" . $groupOutput;
                            $zeroFlag = false; // 避免重复添加零
                        }
                    }
                }
                $groupOutput = rtrim($groupOutput, "零"); // 去除右侧多余的零
                if (!empty($groupOutput)) {
                    $output = $groupOutput . $bigUnits[$i] . $output;
                }
            }
        }

        // 处理小数部分
        if ($fraction != '') {
            $fraction = str_pad($fraction, 2, '0', STR_PAD_RIGHT); // 补齐小数位至两位
            $jiao = $fraction[0];
            $fen = $fraction[1];
            if ($integer > 0) {
                $output .= "元";
            } else {
                // 特别处理只有小数部分的情况，确保输出不会乱码
                $output .= " ";
            }
            $output .= ($jiao > 0 ? $numbersUp[$jiao] . "角" : "零");
            $output .= ($fen > 0 ? $numbersUp[$fen] . "分" : ($jiao > 0 ? "整" : ""));
        } else {
            // 如果没有小数部分，且有整数部分
            if ($integer > 0) {
                $output .= "元整";
            }
        }

        $output = preg_replace('/零(拾|佰|仟|万|亿|万亿)+/u', '零', $output);
        $output = preg_replace('/零+/u', '零', $output);
        $output = preg_replace('/零元/u', '元', $output); // 修正零元为元
        $output = preg_replace('/亿万/u', '亿', $output); // 修正亿万为亿
        $output = trim($output, '零');
        $output = $output ?: $numbersUp[0] . "元整"; // 如果结果为空，则输出“零元整”

        return $output;
    }

    /**
     * 生成随机字符串
     * @param int $length
     * @return string
     */
    public static function getRandStr($length = 16)
    {
        $str = '';
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[mt_rand(0, $max)];
        }
    }

    /**
     * 生成UUID V4
     * @return string
     */
    public static function uuidv4()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

}
