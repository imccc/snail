<?php
namespace Imccc\Snail\Traits;
trait DebugTrait
{
    protected static $debugIndex = 0;
    protected static $_debugInfo = [];
    protected static $styles = [
        'debug' => 'background-color: #ddddff; color: #000; padding: 10px; margin: 10px; border: 1px solid #0000ff;',
        'title' => 'background-color: #dddddd; color: #000; padding: 10px; margin: 10px;',
    ];
    protected static $templates = [
        'debug' => '<div style="{{style}}"> <h3 style="{{title}}"> Debug Information from {{class}}<span style="float:right">#{{index}}</span></h3><div style="padding: 10px"><pre>{{info}}</pre></div></div>',
        'simple' => '<h1>Oops, something went wrong!</h1><p>Please contact the administrator for assistance.</p>',
    ];

    protected static function debug($info)
    {
        if (defined('DEBUG') && DEBUG['debug']) {
            if (empty($info)){
                $infoStr = self::$_debuginfo ;
            }else{
                $infoStr = print_r($info, true);
            }
            $str = str_replace(
                ['{{style}}', '{{title}}', '{{class}}', '{{index}}', '{{info}}'],
                [self::$styles['debug'], self::$styles['title'], self::class, self::getDebugIndex(), $infoStr],
                self::$templates['debug']
            );
            echo $str;
        }
    }
  
    protected static function bindDebugInfo($key, $value)
    {
        self::$_debugInfo[self::class][$key] = $value;
    }

    private static function getDebugIndex()
    {
        return self::$debugIndex++;
    }
}
