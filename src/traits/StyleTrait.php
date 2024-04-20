<?php
namespace Imccc\Snail\Traits;

trait StyleTrait
{
    public static function debugSytle()
    {
        header("Content-type: text/css; charset=utf-8");
        echo "

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
        }";
    }

    public static function handleStyle()
    {
        header("Content-type: text/css; charset=utf-8");
        echo "
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

        ";
    }
}
