<?php

trait MumberTrait
{
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
     * 生成随机数字
     *
     * @param int $length 生成数字的长度
     * @return string 生成的随机数字
     */
    public static function randomNumber($length = 6)
    {
        $numbers = range(0, 9);
        shuffle($numbers);
        $numbers = array_slice($numbers, 0, $length);
        return implode('', $numbers);
    }

    /**
     * 数字转中文
     *
     * @param int $number 要转换的数字
     * @return string 转换后的中文数字
     */
    public static function numberToChinese($number)
    {
        // 数据类型和范围检查
        if (!is_int($number) || $number < 0) {
            throw new InvalidArgumentException('输入必须是非负整数');
        }

        $chineseNumbers = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九'];
        $chineseUnits = ['', '十', '百', '千', '万', '亿', '兆', '京', '垓', '秭', '穰', '沟', '涧', '正', '载', '极'];

        $chinese = '';
        $numberStr = str_pad($number, 16, '0', STR_PAD_LEFT); // 限制最大长度，优化性能
        $length = strlen($numberStr);

        for ($i = 0; $i < $length; $i++) {
            $digit = $numberStr[$i];
            $unit = $length - $i - 1;

            // 添加零的特殊处理逻辑
            $isZero = ($digit == '0');
            $isUnitMultipleOfFour = ($unit % 4 == 0 && $digit != '0');
            $specialZeroHandling = ($isZero && ($unit == 4 || $unit == 8 || $unit == 12 || $unit == 16 || $unit == 20 || $unit == 24 || $unit == 28 || $unit == 32 || $unit == 36 || $unit == 40 || $unit == 44 || $unit == 48));

            if ($isUnitMultipleOfFour || $specialZeroHandling) {
                $chinese .= '零';
            }

            $chinese .= $chineseNumbers[$digit] . $chineseUnits[$unit];

            // 简化处理万和亿的逻辑
            if ($unit >= 4 && $digit == '0') {
                $chinese .= $chineseUnits[$unit];
            }
        }

        // 移除开头多余的零
        $chinese = ltrim($chinese, '零');

        // 对于数字0特殊情况处理
        if ($number == 0) {
            $chinese = '零';
        }

        return $chinese;
    }
}
