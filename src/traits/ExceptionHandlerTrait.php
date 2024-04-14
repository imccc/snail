<?php

namespace Imccc\Snail\Traits;

use ErrorException;
use SplFileObject;
use Throwable;

trait ExceptionHandlerTrait
{
    // 错误计数器
    private static $errorCount = 0;
    private static $debugIndex = 0;

    // 处理异常的方法
    public static function handleException($exceptionOrErrorCode): void
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
    }

    // 显示调试信息
    public static function showDebug(String $exception): void
    {
        self::$debugIndex++;
        // 根据调试模式显示详细错误信息或简单提示
        if (DEBUG['debug'] ?? false) {
            echo '<div style="' . self::getDebugStyle() . '">';
            echo '<h3 style="' . self::getTitleStyle() . '"> Snail Debug <small> - ' . $_SERVER['HTTP_HOST'] . '</small><span style="float:right;">#' . self::getDebugIndex() . '</span></h3>';
            echo '<div style="padding: 10px;">';
            echo $exception;
            echo '</div></div>';
        }
    }

    // 显示错误信息
    public static function showError(Throwable $exception): void
    {
        echo '<div style="' . self::getErrorStyle() . '">';
        echo '<h3 style="' . self::getTitleStyle() . '"> Snail Debug <small> - ' . $_SERVER['HTTP_HOST'] . '</small><span style="float:right;">#' . self::getErrorCount() . '</span></h3>';
        echo '<div style="padding: 10px;">';

        // 根据调试模式显示详细错误信息或简单提示
        if (DEBUG['debug'] ?? false) {
            self::showDetailedError($exception);
        } else {
            echo '<h1>Oops, something went wrong!</h1>';
            echo '<p>Please contact the administrator for assistance.</p>';
        }
        echo '</div></div>';
    }

    // 获取错误信息样式
    protected static function getErrorStyle(): string
    {
        return 'color: dark-grey; border: 1px dashed red; margin: 30px;';
    }

    // 获取信息息样式
    protected static function getDebugStyle(): string
    {
        return 'color: #66ff00; border: 1px dashed green; margin: 30px; background-color: #111; font-size:0,75rem;';
    }

    // 获取错误标题样式
    protected static function getTitleStyle(): string
    {
        return 'color: red; background-color: #eee; margin:0;padding: 10px;';
    }

    // 显示详细错误信息
    protected static function showDetailedError(Throwable $exception): void
    {
        $errorCode = self::extractErrorCode($exception);

        // 错误类型映射
        $errorTypeMap = [
            '0' => 'EXCEPTION', '1' => 'ERROR', '2' => 'WARNING', '4' => 'PARSE',
            '8' => 'NOTICE', '16' => 'CORE_ERROR', '32' => 'CORE_WARNING', '64' => 'COMPILE_ERROR',
            '128' => 'COMPILE_WARNING', '256' => 'USER_ERROR', '512' => 'USER_WARNING',
            '1024' => 'USER_NOTICE', '2048' => 'STRICT', '4096' => 'RECOVERABLE_ERROR',
            '8192' => 'DEPRECATED', '16384' => 'USER_DEPRECATED',
        ];

        // 显示错误类型和堆栈跟踪
        echo '<p>Error Type: ' . $errorTypeMap[$errorCode] . '</p>';
        echo '<p>Stack Trace:</p>';
        echo '<pre style="color:blue">' . self::formatStackTrace($exception->getTrace()) . '</pre>';
        // echo '<p>Original Stack Trace:</p>';
        // echo '<p style="color:gray">' . $exception->getTraceAsString() . '</p>';
    }

    // 提取错误码
    protected static function extractErrorCode(Throwable $exception): int
    {
        $parts = explode(":", $exception->getMessage());
        return count($parts) > 1 ? (int) trim($parts[1]) : 0;
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

    // 获取错误计数
    public static function getErrorCount(): int
    {
        return self::$errorCount;
    }

    // 获取错误计数
    public static function getDebugIndex(): int
    {
        return self::$debugIndex;
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
