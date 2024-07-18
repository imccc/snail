<?php

namespace Imccc\Snail\Helpers;

class ImageHelper
{
    /**
     * 缩放图片
     *
     * @param string $src 源图片路径
     * @param string $dst 目标图片路径
     * @param int $width 目标宽度
     * @param int $height 目标高度
     * @param int $quality 图片质量（1-100）
     * @return bool 是否成功缩放图片
     */
    public static function resize($src, $dst, $width, $height, $quality = 75)
    {
        $srcImage = self::createImage($src);
        if (!$srcImage) {
            return false;
        }

        $resizedImage = imagescale($srcImage, $width, $height);
        if (!$resizedImage) {
            return false;
        }

        $result = self::saveImage($resizedImage, $dst, $quality);
        imagedestroy($srcImage);
        imagedestroy($resizedImage);

        return $result;
    }

    /**
     * 将图片转换为 base64 编码
     *
     * @param string $path 图片路径
     * @return string base64 编码的图片
     */
    public static function toBase64($path)
    {
        $imageData = file_get_contents($path);
        return 'data:image/jpeg;base64,' . base64_encode($imageData);
    }

    /**
     * 将 base64 编码的图片保存为文件
     *
     * @param string $base64 base64 编码的图片数据
     * @param string $path 保存路径
     * @return bool 是否成功保存图片
     */
    public static function base64ToFile($base64, $path)
    {
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));
        if (!$imageData) {
            return false;
        }

        return file_put_contents($path, $imageData) !== false;
    }

    /**
     * 创建图像资源
     *
     * @param string $path 图像路径
     * @return resource|false 图像资源或者失败时返回 false
     */
    private static function createImage($path)
    {
        $imageInfo = getimagesize($path);
        if (!$imageInfo) {
            return false;
        }

        $imageType = $imageInfo[2];
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($path);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($path);
            default:
                return false;
        }
    }

    /**
     * 保存图像资源到文件
     *
     * @param resource $image 图像资源
     * @param string $path 保存路径
     * @param int $quality 图片质量（1-100）
     * @return bool 是否成功保存图片
     */
    private static function saveImage($image, $path, $quality = 75)
    {
        $imageType = exif_imagetype($path);
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                return imagejpeg($image, $path, $quality);
            case IMAGETYPE_PNG:
                return imagepng($image, $path);
            case IMAGETYPE_GIF:
                return imagegif($image, $path);
            default:
                return false;
        }
    }
}
