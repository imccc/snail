<?php
namespace Imccc\Snail\Traits;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Traits\StyleTrait;

trait DebugTrait
{
    protected static $debugIndex = 0; // 调试信息索引
    protected static $_debugInfo = []; // 存储调试信息
    protected static $debugStyleOutput = false; // 用于记录样式是否已经输出
    use StyleTrait;
    protected static $templates = [
        'debug' => '<div class="debugCentent"> <h3 class="debugTitle"> {{class}}<span style="float:right">#{{index}}</span></h3><div style="padding: 10px"><pre>{{info}}</pre></div></div>',
        'simple' => '<h1>Oops, something went wrong!</h1><p>Please contact the administrator for assistance.</p>',
        'banner' => '<div class="debugBanner"><h1>Snail Framework Debug</h1></div><div class="debugNow">{{$now}}</div>',
    ];

    protected static function debug($info = '')
    {
        if (!self::$debugStyleOutput) { // 如果样式未输出，则输出样式
            echo self::debugStyle(); // 输出样式
            self::$debugStyleOutput = true; // 设置样式已经输出
        }

        if (self::$debugIndex == 0) {
            $now = date('Y-m-d H:i:s');
            echo str_replace('{{$now}}', $now, self::$templates['banner']);
        }

        self::$debugIndex++;

        if (defined('SNAIL_DEBUG') && SNAIL_DEBUG['debug'] ?? false) {
            $infoStr = $info ? $info : self::getDebugInfo();
            $str = str_replace(
                ['{{class}}', '{{index}}', '{{info}}'],
                [self::class, self::getDebugIndex(), print_r($infoStr, true)],
                self::$templates['debug']
            );
            echo $str;
            if (SNAIL_DEBUG['log']) {
                self::debuglog($infoStr);
            }
        }
    }

    protected static function bindDebugInfo($key, $value)
    {
        self::$_debugInfo[self::class][$key] = $value;
    }

    private static function getDebugIndex()
    {
        return self::$debugIndex;
    }

    protected static function getDebugInfo()
    {
        return self::$_debugInfo;
    }


    protected static function debuglog($infoStr)
    {
        $container = Container::getInstance();
        // 获取当前方法所在的类和方法名称
        $method = __METHOD__;
        list($class, $methodName) = explode('::', $method) ?? [];

        //尝试使用容器获取日志服务
        $logService = $container->resolve('LoggerService');
        if ($logService) {
            $logService->log($infoStr, $methodName);
        }
    }

}
