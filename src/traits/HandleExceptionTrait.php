<?php
namespace Imccc\Snail\Traits;

use ErrorException;
use SplFileObject;
use Throwable;

trait HandleExceptionTrait
{
    protected static $handleStyleOutput = false; // 用于记录样式是否已经输出

    protected static $errorCount = 0;
    public static $handleTpl = [
        'error' => '<div class="handleError"><h3 class="handleTitle"> <small>{{$from}}</small><span style="float:right"># {{$index}}</span></h3><div class="handleContent">{{$info}}</div></div>',
        'simple' => '<h1>Oops, something went wrong!</h1><p>Please contact the administrator for assistance.</p>',
        'detail' => '<p class="handleTrace">Strack Trace: {{$trace}}</p><pre class="handleContent">{{$err}}</pre>',
        'banner' => '<div class="handleBanner"><h1>Snail Framework HandlerException</h1></div><div class="handleNow">{{$now}}</div>',
    ];

    // 处理异常的方法
    public static function handleException($exceptionOrErrorCode, $string = ''): void
    {
        if (!self::$handleStyleOutput) { // 如果样式未输出，则输出样式
            echo self::handleStyle();
            self::$handleStyleOutput = true; // 设置样式已经输出
        }

        if (self::$errorCount == 0) {
            $now = date('Y-m-d H:i:s');
            echo str_replace('{{$now}}', $now, self::$handleTpl['banner']);
        }

        // 增加错误计数
        self::$errorCount++;

        // 根据传入参数类型创建异常对象
        if ($exceptionOrErrorCode instanceof Throwable) {
            $exception = $exceptionOrErrorCode;
        } else {
            $errorMessage = is_string($exceptionOrErrorCode) ? $exceptionOrErrorCode : "Unknown error";
            $exception = new ErrorException("Error: " . $errorMessage);
        }

        // 显示错误信息
        self::showError($exception);
        // 记录错误日志
        self::logError($exception);
    }

    /**
     * 显示错误信息
     */
    public static function showError(Throwable $exception): void
    {
        if (defined('SNAIL_DEBUG') && SNAIL_DEBUG['debug'] ?? false) {
            // 根据调试模式显示详细错误信息或简单提示
            $str = str_replace(['{{$from}}', '{{$index}}', '{{$info}}'], [self::class, self::$errorCount, self::showDetailedError($exception)], self::$handleTpl['error']);
        } else {
            $str = self::$handleTpl['simple'];
        }
        echo $str;
    }

    public static function showDetailedError($exception)
    {
        $str = str_replace(['{{$trace}}', '{{$err}}'], [$exception->getTraceAsString(), self::formatStackTrace($exception->getTrace())], self::$handleTpl['detail']);
        return $str;
    }

    // 格式化堆栈跟踪信息
    private static function formatStackTrace(array $trace): string
    {
        $formattedTrace = '';
        foreach ($trace as $index => $item) {
            $file = $item['file'] ?? '';
            $line = $item['line'] ?? '';
            $class = $item['class'] ?? '';
            $function = $item['function'] ?? '';

            $formattedTrace .= "第{$index}层调用：\n";
            $formattedTrace .= "文件：{$file} 行：{$line}\n";
            $formattedTrace .= "类名：{$class} 函数：{$function}\n";

            if (isset($item['args']) && is_array($item['args']) && !empty($item['args'])) {
                // 过滤非字符串参数
                $args = array_filter($item['args'], function ($arg) {
                    return is_string($arg);
                });
                if (!empty($args)) {
                    // 显示参数错误信息
                    $formattedArgs = implode(', ', $args);
                    $formattedTrace .= "<span style='color: red'>错误： 参数：{$formattedArgs}</span>\n";
                    // 如果文件和行号不为空，则显示源码
                    if (!empty($file) && !empty($line)) {
                        $sourceCode = self::getSourceCodeLine($file, $line);
                        if ($sourceCode !== null) {
                            $formattedTrace .= "<span style='color: green'>源码：{$sourceCode}</span>\n";
                        }
                    }
                }
            }
            $formattedTrace .= "\n";
        }
        return $formattedTrace;
    }

    // 获取指定文件指定行的源码
    protected static function getSourceCodeLine(string $filePath, int $lineNumber): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }
        $file = new SplFileObject($filePath);
        $file->seek($lineNumber - 1);
        return $file->valid() ? $file->current() : null;
    }

    public static function handleStyle()
    {
        $style = "<style>
        /** 错误处理 */

        .handleBanner, .handleError,.handleNow {
            margin: 10px 30px;
            border-radius: 5px;
            color: #FFFFFF;
        }

        .handleTrace {
            padding: 20px;
            margin: 0;
        }

        .handleBanner {
            padding: 5px;
            background-color: #FF8800;
            text-align: center;
        }

        .handleTitle {
            background-color: #e7c090;
            color: #50290d;
            padding: 10px 16px;
            margin-bottom:0;
            border-bottom: 1px solid #fcb322;
            border-radius: 5px 5px 0 0;
        }

        .handleError {
            background-color: #f1d7c4db;
            color: #45350b;
        }

        .handleTrace {
            background-color: #f6f2e2;
            border-bottom: 1px solid #fcb322;
            color: #000000;
            white-space: pre-wrap; /* 添加自动换行 */
        }

        .handleNow {
            color: #000000;
            text-align: end;
        }

        .handleContent {
            padding: 10px;
            white-space: pre-wrap; /* 添加自动换行 */
        }
        </style>
        ";
        return $style;
    }

    // 记录错误日志
    private static function logError(Throwable $exception): void
    {
        $log = sprintf(
            "[%s] (%s) [%s] [%d] [%s]",
            $exception->getMessage(),
            $exception->getCode(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        error_log($log);
    }

}
