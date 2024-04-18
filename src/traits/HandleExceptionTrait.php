<?php
namespace Imccc\Snail\Traits;

use ErrorException;
use SplFileObject;
use Throwable;

trait HandleExceptionTrait
{
    private static $errorCount = 0;
    protected static $handleStyle = [
        'error' => 'background-color: #ffdddd; color: #000; padding: 10px; margin: 10px; border: 1px solid #ff0000;',
        'debug' => 'background-color: #ddddff; color: #000; padding: 10px; margin: 10px; border: 1px solid #0000ff;',
        'title' => 'background-color: #dddddd; color: #000; padding: 10px; margin: 10px;',
    ];
    public static $handleTpl = [
        'error' => '<div style="{{$errorstyle}}"> <h3 style="{{$titlestyle}}"> Snail Error <span style="float:right"># {{$index}}</span></h3><div style="padding: 10px">{{$info}}</div></div>',
        'simple' => '<h1>Oops, something went wrong!</h1><p>Please contact the administrator for assistance.</p>',
        'detail' => '<p>Strack Trace: {{$trace}}</p><pre style="background-color: #eee;">{{$err}}</pre>',
    ];

    // 处理异常的方法
    public static function handleException($exceptionOrErrorCode, $string = ''): void
    {
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
        // if (!empty($string)) {
        //     echo " -=[ $string ]=- ";
        // }

    }

    /**
     * 显示错误信息
     */
    public static function showError(Throwable $exception): void
    {
        if (DEBUG['debug'] ?? false) {
            // 根据调试模式显示详细错误信息或简单提示
            $str = str_replace(['{{$errorstyle}}', '{{$titlestyle}}',  '{{$info}}'], [self::$handleStyle['error'], self::$handleStyle['title'],  self::showDetailedError($exception)], self::$handleTpl['error']);
        } else {
            $str = self::$handleTpl['simple'];
        }
        echo $str;
    }

    public static function showDetailedError($exception)
    {
        $str = str_replace(['{{$trace}}', '{{$err}}'], [$exception->getTraceAsString(), self::formatStackTrace($exception->getTrace())], self::$handleTpl['detail']);
        echo $str;
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
