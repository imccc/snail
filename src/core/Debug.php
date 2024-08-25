<?php
namespace Imccc\Snail\Core;

use ErrorException;
use Throwable;

class Debug
{
    /**
     * 异常处理
     * @param Throwable|int|string $exceptionOrErrorCode
     * @return void
     */
    public static function handleException($exceptionOrErrorCode)
    {
        // 根据传入参数类型创建异常对象
        if ($exceptionOrErrorCode instanceof Throwable) {
            $exception = $exceptionOrErrorCode;
        } else {
            $errorMessage = is_string($exceptionOrErrorCode) ? $exceptionOrErrorCode : "Unknown error";
            $exceptionCode = is_int($exceptionOrErrorCode) ? $exceptionOrErrorCode : 0;
            $exception = new ErrorException($errorMessage, $exceptionCode);
        }

        // 记录错误日志
        self::logError($exception);

        // 抛出异常
        throw $exception;
    }

    /**
     * 使用模板处理出错信息
     * @param $e
     * @param $info
     */
    public static function errorOutput($e = 'unknow', $info = '', $tpl = '')
    {
        if ($tpl == '') {
            $tpl = '<html><head><title>%s</title></head><body style="margin:20vh; padding:10vh; border:1px dotted #333"><h1>%s</h1><br><b>%s</b><br><br><div>The great AI creates a new world!<br>By: Snail Boot</div></body></html>';
        }
        switch ($e) {
            case '404':
                http_response_code(404);
                $html = sprintf($tpl, $e, $e . " Not Found.", $info);
                break;
            case '403':
                http_response_code(403);
                $html = sprintf($tpl, $e, $e . " Forbidden.", $info);
                break;
            case '500':
                http_response_code(500);
                $html = sprintf($tpl, $e, $e . " Internal Server Error.", $info);
                break;
            default:
                http_response_code(500);
                $html = sprintf($tpl, $e, "Unknow Internal Server Error.", $info);
        }
        echo $html;
    }

    /**
     * 调试输出
     * @param mixed $data
     * @param bool $style
     * @param bool $die
     * @param bool $echo
     * @return void|string
     */
    public static function dump($data, $style = false, $die = false, $echo = true)
    {
        ob_start();
        $type = gettype($data);

        $output = '';
        if ($style) {
            $output .= '<style>
           pre {
                background-color: #e0f7de; /* 浅水果绿 */
                padding: 10px;
                border-radius: 5px;
                margin-bottom: 10px;
            }

            hr {
                border: none;
                border-top: 1px solid #000; /* 你可以根据需要调整颜色 */
                margin: 5px 0; /* 你可以根据需要调整间距 */
            }

            div.debug-container {
                background-color: #e0f7de; /* 浅水果绿 */
                padding: 10px;
                border-radius: 5px;
                margin-bottom: 10px;
            }

            h3.debug-header {
                margin: 0;
                margin-bottom: 10px;
                color: #e0f7de; /* 水果绿 */
                background-color: #81c784;
                padding: 10px;
            }

            </style>';
            $output .= '<h3 class="debug-header">Snail Debug Dump Data Type:  ' . strtoupper($type) . '</h3>';
            $output .= '<div class="debug-container">';

        }

        switch ($type) {
            case 'integer':
            case 'double': // double 是 float 的别名
            case 'string':
                $output .= htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
                break;
            case 'object':
            case 'array':
                $output .= '<h3>PRINT_R:</h3><pre>' . print_r($data, true) . '</pre>';
                $output .= '<h3>VAR_EXPORT:</h3><pre>' . var_export($data, true) . '</pre>';
                break;
            case 'null':
            case 'boolean':
                ob_start();
                var_dump($data);
                $output .= ob_get_clean();
                break;
            case 'resource':
                $output .= 'Resource of type: ' . get_resource_type($data);
                break;
            case 'callable':
                $output .= 'Callable type';
                break;
            default:
                $output .= 'Unknown type: ' . $type;
                break;
        }

        if ($style) {
            $output .= '</div>';
        }

        ob_end_clean();

        $output .= '<h3 class="debug-header">Debug BackTrace:</h3>';

        if ($echo) {
            echo $output;
        } else {
            return $output;
        }
        self::debug_backtrace();

        if ($die) {
            die();
        }
    }

    /**
     * 获取调用栈信息
     */
    public static function debug_backtrace()
    {
        $trace = debug_backtrace();
        echo '<pre>';
        foreach ($trace as $line) {
            echo "Line: {$line['line']}  at File: {$line['file']}<br>";
            echo "Class: {$line['class']}  Function: {$line['function']} <br><hr>";
        }
        echo '</pre>';
    }

    /**
     * 记录错误日志
     */
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
