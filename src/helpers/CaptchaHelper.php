<?php

namespace Imccc\Snail\Helpers;

class CaptchaHelper
{
    private $lineWidth;
    private $lineNum;
    private $dotR;
    private $dotNum;
    private $preGroundColor;
    private $backGroundColor;
    private $fontColorRange;
    private $fontSize;
    private $fontStyle;
    private $chinesecontent;
    private $englishcontent;
    private $length;
    private $text;
    private $width;
    private $height;
    private $fontFile;
    private $outline;
    private $useChinese;

    public function __construct($params = [])
    {
        $defaults = [
            'lineWidth' => 2,
            'lineNum' => 9,
            'dotR' => 2,
            'dotNum' => 200,
            'preGroundColor' => [10, 80],
            'backGroundColor' => [150, 250],
            'fontColorRange' => [10, 80],
            'fontSize' => 30,
            'fontStyle' => 'fill',
            'chinesecontent' => '九方科技外贸通acdefhijkmnpwxyABCD牛EFGHJKMNPQWXY12345789',
            'englishcontent' => 'acdefhijkmnpwxyABCDEFGHJKMNPQWXY12345789',
            'length' => 4,
            'width' => 180,
            'height' => 60,
            'outline' => 1,
            'fontFile' => './Arial_Unicode.ttf' // 修改为通用字体文件的路径
        ];
        $params = array_merge($defaults, $params);
        foreach ($params as $key => $value) {
            $this->$key = $value;
        }
        $this->useChinese = file_exists($this->fontFile);
        $this->content = $this->useChinese ? $this->chinesecontent : $this->englishcontent;
    }

    private function getRandom($min, $max)
    {
        // 确保 max 大于 min
        return mt_rand(max($min, 0), max($min, $max));
    }

    private function getColor($range)
    {
        return [
            $this->getRandom($range[0], $range[1]),
            $this->getRandom($range[0], $range[1]),
            $this->getRandom($range[0], $range[1])
        ];
    }

    private function getText()
    {
        $length = mb_strlen($this->content);
        $str = '';
        for ($i = 0; $i < $this->length; $i++) {
            $str .= mb_substr($this->content, $this->getRandom(0, $length - 1), 1);
        }
        $this->text = $str;
        return $str;
    }

    private function drawLine($image)
    {
        imagesetthickness($image, $this->lineWidth); // 设置线宽
        for ($i = 0; $i < $this->lineNum; $i++) {
            $color = $this->getColor($this->preGroundColor);
            $lineColor = imagecolorallocate($image, $color[0], $color[1], $color[2]);
            imageline($image,
                $this->getRandom(0, imagesx($image)),
                $this->getRandom(0, imagesy($image)),
                $this->getRandom(0, imagesx($image)),
                $this->getRandom(0, imagesy($image)),
                $lineColor
            );
        }
    }

    private function drawDots($image)
    {
        for ($i = 0; $i < $this->dotNum; $i++) {
            $color = $this->getColor($this->preGroundColor);
            $dotColor = imagecolorallocate($image, $color[0], $color[1], $color[2]);
            $cx = $this->getRandom(0, imagesx($image));
            $cy = $this->getRandom(0, imagesy($image));
            imagefilledellipse($image, $cx, $cy, $this->dotR * 2, $this->dotR * 2, $dotColor);
        }
    }

    private function drawTextWithOutline($image, $size, $angle, $x, $y, $fontColor, $fontFile, $text)
    {
        $backgroundColor = imagecolorallocate($image, ...$this->getColor($this->backGroundColor)); // 使用背景色作为描边颜色
        $outlineWidth = $this->outline;
        for ($ox = -$outlineWidth; $ox <= $outlineWidth; $ox++) {
            for ($oy = -$outlineWidth; $oy <= $outlineWidth; $oy++) {
                imagettftext($image, $size, $angle, $x + $ox, $y + $oy, $backgroundColor, $fontFile, $text);
            }
        }
        imagettftext($image, $size, $angle, $x, $y, $fontColor, $fontFile, $text);
    }

    private function drawText($image)
    {
        $text = $this->getText();
        $fontColor = $this->getColor($this->fontColorRange);
        $fontColor = imagecolorallocate($image, $fontColor[0], $fontColor[1], $fontColor[2]);

        if ($this->useChinese) {
            for ($i = 0; $i < mb_strlen($text); $i++) {
                $char = mb_substr($text, $i, 1);

                // 获取字符的边界
                $bbox = imagettfbbox($this->fontSize, 0, $this->fontFile, $char);
                $charWidth = $bbox[2] - $bbox[0];
                $charHeight = $bbox[1] - $bbox[7];

                $x = $this->getRandom($i * $this->width / $this->length + 5, ($i + 1) * $this->width / $this->length - $charWidth - 5);
                $y = $this->getRandom($this->height / 2 + $charHeight / 2, $this->height / 2 + $charHeight / 2 + 15);
                $angle = $this->getRandom(-10, 10);

                // 调整文本位置确保它在图像内
                if ($x + $charWidth > $this->width - 5) {
                    $x = $this->width - $charWidth - 5;
                }
                if ($y - $charHeight < 5) {
                    $y = $charHeight + 5;
                }

                $this->drawTextWithOutline($image, $this->fontSize, $angle, $x, $y, $fontColor, $this->fontFile, $char);
            }
        } else {
            for ($i = 0; $i < strlen($text); $i++) {
                $x = $this->getRandom($i * $this->width / $this->length + 5, ($i + 1) * $this->width / $this->length - 10);
                $y = $this->getRandom($this->height / 2 + 5, $this->height / 2 + 15);

                // 确保文本在图像内部
                if ($x + 10 > $this->width - 5) {
                    $x = $this->width - 15;
                }
                if ($y > $this->height - 5) {
                    $y = $this->height - 5;
                }

                imagestring($image, 5, $x, $y, $text[$i], $fontColor);
            }
        }
    }

    public function draw()
    {
        $image = imagecreatetruecolor($this->width, $this->height);

        $backgroundColor = $this->getColor($this->backGroundColor);
        $backgroundColor = imagecolorallocate($image, $backgroundColor[0], $backgroundColor[1], $backgroundColor[2]);
        imagefill($image, 0, 0, $backgroundColor);

        $this->drawText($image);
        $this->drawLine($image);
        $this->drawDots($image);

        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
    }

    public function getCaptchaText()
    {
        return $this->text;
    }
}
