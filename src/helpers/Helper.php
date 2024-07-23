<?php
namespace Imccc\Snail\Helpers;

class Helper
{
    public function __construct()
    {
        spl_autoload_register([$this, 'loadhelper']);
    }
    public function loadhelper($class)
    {
        // 定义助手函数文件所在目录
        $helperDirectory = $_SERVER['DOCUMENT_ROOT'] . '/helpers/';
        $helperFile = $helperDirectory . 'fun.php';
        if (file_exists($helperFile)) {
            require_once $helperFile;
        }
    }
}
