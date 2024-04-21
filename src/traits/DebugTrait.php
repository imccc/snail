<?php
namespace Imccc\Snail\Traits;

use Imccc\Snail\Core\Container;

trait DebugTrait
{
    protected static $debugIndex = 0; // 调试信息索引
    protected static $_debugInfo = []; // 存储调试信息
    protected static $debugStyleOutput = false; // 用于记录样式是否已经输出

    // 调试信息模板
    protected static $templates = [
        'debug' => '<div class="debugCentent"> <h3 class="debugTitle"> {{class}}<span style="float:right">#{{index}}</span></h3><div style="padding: 10px"><pre>{{info}}</pre></div></div>',
        'simple' => '<h1>Oops, something went wrong!</h1><p>Please contact the administrator for assistance.</p>',
        'banner' => '<div class="debugBanner"><h1>Snail Framework Debug</h1></div><div class="debugNow">{{$now}}</div>',
    ];

    /**
     * 输出调试信息
     * @param string $info
     * @return void
     */
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
                $method = __METHOD__;
                $methodName = list($class, $methodName) = explode('::', $method);
                self::debugLog($infoStr, $methodName);
                self::debugLog1($infoStr);

            }
        }
    }

    /**
     * 绑定调试信息
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    protected static function bindDebugInfo($key, $value)
    {
        self::$_debugInfo[self::class][$key] = $value;
    }

    /**
     * 获取调试索引
     *
     * @return int
     */
    private static function getDebugIndex()
    {
        return self::$debugIndex;
    }

    /**
     * 获取调试信息
     *
     * @return array
     */
    protected static function getDebugInfo()
    {
        return self::$_debugInfo;
    }

    /**
     * 输出调试信息样式表
     *
     * @return string
     */
    public static function debugStyle()
    {
        // 在输出样式之前确保没有其他输出
        $style = "
        <style>

        /** 调试模式样式 */

        .debugBanner, .debugCentent,.debugNow {
            margin: 10px 30px;
            border-radius: 5px;
            color: #FFFFFF;
        }

        .debugBanner {
            padding: 5px;
            background-color: #67c295;
            text-align: center;
        }

        .debugTitle {
            background-color: #ade3c8;
            color: #50290d;
            padding: 10px 16px;
            margin-top: 0;
            margin-bottom:0;
            border-bottom: 1px solid #67c295;
            border-radius: 5px 5px 0 0;
        }

        .debugCentent {
            background-color: #90e7bb;
            color: #45350b;
        }

        .debugNow {
            color: #000000;
            text-align: end;
        }
        </style>
        ";

        return $style;
    }

    /**
     * 记录日志
     * @param string $infoStr
     * @return void
     */
    protected static function debugLog($infoStr, $methodName)
    {
        $container = Container::getInstance();
        // 获取当前方法所在的类和方法名称
        $info = print_r($infoStr, true);
        //尝试使用容器获取日志服务
        $logService = $container->resolve('LoggerService');
        if ($logService) {
            $logService->log($info, $methodName);
        } else {
            error_log("__DebugTrait__:" . $methodName . $info);
        }
    }

    /**
     * 另一种记录日志
     * @param string $infoStr
     * @return void
     */
    protected static function debugLog1($infoStr)
    {
        $container = Container::getInstance();
        // 获取调用该方法的类名
        $callerClass = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'];
        // 获取当前方法所在的类和方法名称
        $info = print_r($infoStr, true);
        //尝试使用容器获取日志服务
        $logService = $container->resolve('LoggerService');
        if ($logService) {
            $logService->log($info, $callerClass);
        } else {
            error_log("__DebugTrait__:" . $callerClass . $info);
        }
    }

}
