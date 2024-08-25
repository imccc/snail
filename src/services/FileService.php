<?php
/**
 * 文件服务
 *
 * @package Imccc\Snail\Services
 * @version 0.0.1
 * @author Imccc
 * @copyright Copyright (c) 2024 Imccc.
 */

namespace Imccc\Snail\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class FileService
{
    /**
     * 删除文件
     *
     * @param string $filename 文件路径
     * @return bool 是否成功删除文件
     */
    public function deleteFile($filename)
    {
        if (is_file($filename)) {
            return unlink($filename);
        }
        return false;
    }

    /**
     * 删除目录
     *
     * @param string $directory 目录路径
     * @return bool 是否成功删除目录
     */
    public function deleteDirectory($directory)
    {
        if (!is_dir($directory)) {
            return false;
        }
        $files = array_diff(scandir($directory), ['.', '..']);
        foreach ($files as $file) {
            is_dir("$directory/$file") ? $this->deleteDirectory("$directory/$file") : unlink("$directory/$file");
        }
        return rmdir($directory);
    }

    /**
     * 清空目录
     *
     * @param string $directory 目录路径
     * @return bool 是否成功清空目录
     */
    public function clearDirectory($directory)
    {
        if (!is_dir($directory)) {
            return false;
        }
        $files = array_diff(scandir($directory), ['.', '..']);
        foreach ($files as $file) {
            is_dir("$directory/$file") ? $this->deleteDirectory("$directory/$file") : unlink("$directory/$file");
        }
        return true;
    }

    /**
     * 创建目录
     *
     * @param string $path 目录路径
     * @param int $mode 目录权限，默认为 0777
     * @param bool $recursive 是否递归创建父级目录，默认为 true
     * @return bool 是否成功创建目录
     */
    public function createDirectory($path, $mode = 0777, $recursive = true)
    {
        return mkdir($path, $mode, $recursive);
    }

    /**
     * 新建文件
     *
     * @param string $filename 文件名
     * @param string $content 文件内容
     * @return bool 是否成功创建文件
     */
    public function createFile($filename, $content = '')
    {
        return file_put_contents($filename, $content) !== false;
    }

    /**
     * 移动文件
     *
     * @param string $source 源文件路径
     * @param string $destination 目标文件路径
     * @return bool 是否成功移动文件
     */
    public function moveFile($source, $destination)
    {
        return rename($source, $destination);
    }

    /**
     * 重命名文件
     *
     * @param string $oldname 旧文件名
     * @param string $newname 新文件名
     * @return bool 是否成功重命名文件
     */
    public function renameFile($oldname, $newname)
    {
        return rename($oldname, $newname);
    }

    /**
     * 重命名目录
     *
     * @param string $oldname 旧目录名
     * @param string $newname 新目录名
     * @return bool 是否成功重命名目录
     */
    public function renameDirectory($oldname, $newname)
    {
        return rename($oldname, $newname);
    }

    /**
     * 设置文件或目录权限
     *
     * @param string $filename 文件或目录路径
     * @param int $mode 权限值
     * @return bool 是否成功设置权限
     */
    public function setPermission($filename, $mode)
    {
        return chmod($filename, $mode);
    }

    /**
     * 检查文件或目录是否存在
     *
     * @param string $path 文件或目录路径
     * @return bool 文件或目录是否存在
     */
    public function exists($path)
    {
        return file_exists($path);
    }

    /**
     * 移动目录（支持跨分区移动）
     *
     * @param string $source 源目录路径
     * @param string $destination 目标目录路径
     * @return bool 是否成功移动目录
     */
    public function moveDirectory($source, $destination)
    {
        if (!is_dir($source)) {
            return false;
        }
        if (!is_dir($destination)) {
            $this->createDirectory($destination);
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $file) {
            $target = $destination . '/' . $files->getSubPathName();
            if ($file->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target);
                }
            } else {
                rename($file->getPathname(), $target);
            }
        }
        return rmdir($source);
    }

    /**
     * 压缩文件或目录
     *
     * @param string $source 源文件或目录路径
     * @param string $destination 压缩文件保存路径
     * @return bool 是否成功压缩文件或目录
     */
    public function compress($source, $destination)
    {
        $zip = new ZipArchive();
        if ($zip->open($destination, ZipArchive::CREATE) !== true) {
            return false;
        }

        if (is_dir($source)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($source) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
        } elseif (is_file($source)) {
            $zip->addFile($source, basename($source));
        }

        return $zip->close();
    }

    /**
     * 解压文件
     *
     * @param string $source 压缩文件路径
     * @param string $destination 解压目录路径
     * @return bool 是否成功解压文件
     */
    public function decompress($source, $destination)
    {
        $zip = new ZipArchive();
        if ($zip->open($source) !== true) {
            return false;
        }

        $zip->extractTo($destination);
        $zip->close();

        return true;
    }

    /**
     * 获取目录下的所有文件和目录（不包括 . 和 ..）
     *
     * @param string $directory 目录路径
     * @return array 包含文件和目录名的数组
     */
    public function getDirectoryContents($directory)
    {
        if (!is_dir($directory)) {
            return [];
        }
        return array_diff(scandir($directory), ['.', '..']);
    }
}
