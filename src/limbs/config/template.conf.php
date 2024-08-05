<?php
return [
    'engine' => 'snail', // 引擎名称 snail twig
    'cache' => false, // 是否开启缓存
    'path' => APP_PATH . '/{$group}/View/{$controller}/', // 模板路径规则
    'static' => '/Static/', // 静态资源路径
    'library' => [
        '__JQUERY__' => 'jquery/jquery-3.7.1.min.js',
        '__BOOTSTRAP_JS__' => 'bootstrap/js/bootstrap.min.js',
        '__BOOTSTRAP_CSS__' => 'bootstrap/css/bootstrap.min.css',
        '__BOOTSTRAP_THEME_CSS__' => 'bootstrap/css/bootstrap-theme.min.css',
        '__BOOTSTRAP_THEME_JS__' => 'bootstrap/js/bootstrap.min.js',
        '__FONTAWESOME__' => 'fontawesome/css/fontawesome.min.css',
        '__ANIMATE__' => 'animate/animate.min.css',
        '__WOW__' => 'wow/wow.min.js',
    ],
    'default' => 'index',
    'view' => [
        'base' => APP_PATH . '/_base/_view/', // view基础模板路径
        'page' => 'page/',
        'ext' => '.html'
    ],
    'twig' => [
        'ext' => '.twig',
        'base' => APP_PATH . '/_base/_twig/', // Twig 基础模板路径
        'options' => [
            'cache' => dirname($_SERVER['DOCUMENT_ROOT']) . '/runtime/twig_cache', // Twig 缓存路径
            // 其他 Twig 配置选项
            'auto_reload' => true, // 自动重新加载修改过的模板
        ],
    ],
    'snail' => [
        'ext' => '.tpl',
        'base' => APP_PATH . '/_base/_snail/', // snail 基础模板路径
        'tmp' => dirname($_SERVER['DOCUMENT_ROOT']) . '/runtime/temp', // snail 临时路径
        'cache' => dirname($_SERVER['DOCUMENT_ROOT']) . '/runtime/snail_cache', // snail 缓存路径
        'tags' => [
            "{{ if %% }}" => '<?php if(\1): ?>', // if标签
            "{{ else }}" => '<?php else: ?>', // else标签
            "{{ elseif %% }}" => '<?php elseif(\1): ?>', // elseif标签
            "{{ else if %% }}" => '<?php elseif(\1): ?>', // else if标签
            "{{ /if }}" => '<?php endif; ?>', // endif标签
            "{{ foreach %% }}" => '<?php foreach(\1): ?>', // foreach标签
            "{{ /foreach }}" => '<?php endforeach; ?>', // endforeach标签
            "{{ for %% }}" => '<?php for(\1): ?>', // for标签
            "{{ /for }}" => '<?php endfor; ?>', // endfor标签
            "{{ while %% }}" => '<?php while(\1): ?>', // while标签
            "{{ /while }}" => '<?php endwhile; ?>', // endwhile标签
            "{{ switch %% }}" => '<?php switch(\1): ?>', // switch标签
            "{{ case %% }}" => '<?php case \1: ?>', // case标签
            "{{ default }}" => '<?php default: ?>', // default标签
            "{{ endswitch }}" => '<?php endswitch; ?>', // endswitch标签
            "{{ continue }}" => '<?php continue; ?>', // continue标签
            "{{ break }}" => '<?php break; ?>', // break标签
            "{{ upper %% }}" => '<?php echo strtoupper(\1); ?>', // 大写标签
            "{{ lower %% }}" => '<?php echo strtolower(\1); ?>', // 小写标签
            "{{ html %% }}" => '<?php echo htmlspecialchars(\1); ?>', // html标签
            "{{ html_decode %% }}" => '<?php echo htmlspecialchars_decode(\1); ?>', // html_decode标签
            "{{ html_entity_decode %% }}" => '<?php echo html_entity_decode(\1); ?>', // html_entity_decode标签
            "{{ html_entity_encode %% }}" => '<?php echo htmlentities(\1); ?>', // html_entity_encode标签
            "{{ nl2br %% }}" => '<?php echo nl2br(\1); ?>', // nl2br标签
            "{{ trim %% }}" => '<?php echo trim(\1); ?>', // trim标签
            "{{ trim_left %% }}" => '<?php echo ltrim(\1); ?>', // trim_left标签
            "{{ trim_right %% }}" => '<?php echo rtrim(\1); ?>', // trim_right标签
            "{{ trim_all %% }}" => '<?php echo trim(\1, " \t\n\r\0\x0B"); ?>', // trim_all标签
            "{{ trim_all_left %% }}" => '<?php echo ltrim(\1, " \t\n\r\0\x0B"); ?>', // trim_all_left标签
            "{{ trim_all_right %% }}" => '<?php echo rtrim(\1, " \t\n\r\0\x0B"); ?>', // trim_all_right标签
            "{{ $%% }}" => '<?php echo $\1; ?>', // 变量输出标签
            "{{ %% = %% }}" => '<?php \1 = \2; ?>', // 变量赋值标签
            "{{ var %% = %% }}" => '<?php \1 = \2; ?>', // 变量赋值标签
            "{{ $%% = $%% }}" => '<?php $\1 = $\2; ?>', // 变量赋值标签
            "{{ var $%% = $%% }}" => '<?php $\1 = $\2; ?>', // 变量赋值标签
            "{{ $%%++ }}" => '<?php $\1++; ?>', // 变量自增标签
            "{{ $%%-- }}" => '<?php $\1--; ?>', // 变量自减标签
            "{{ die }}" => '<?php die(); ?>', // die标签
            "{{ exit }}" => '<?php exit(); ?>', // exit标签
            "{{ /* }}" => '<?php /** Snail: ' . PHP_EOL, // php代码内注释开始
            "{{ */ }}" => '*/ ?>', // php代码内注释结束
            "{{ ! }}" => PHP_EOL . '<!-- Snail: ', // HTML代码内注释开始
            "{{ /! }}" => ' -->' . PHP_EOL, // HTML代码内注释结束
            "{{ php }}" => '<?php ', // php代码块开始
            "{{ /php }}" => '?>', // php代码块结束
            "{{ js }}" => '<!-- Snail: Js Tag Start --> ' . PHP_EOL . '<script>', // js代码块开始
            "{{ /js }}" => '</script>' . PHP_EOL . '<!-- Snail: Js Tag End -->', // js代码块结束
            "{{ css }}" => '<!-- Snail: Style Tag Start -->' . PHP_EOL . '<style>', // css代码块开始
            "{{ /css }}" => '</style>' . PHP_EOL . '<!-- Snail: Style Tag End -->', // css代码块结束
            "{{ js %% }}" => '<!-- Snail: JS Include --><script src="\1"></script>', // js标签
            "{{ css %% }}" => '<!-- Snail: CSS Link --><link href="\1" rel="stylesheet">', // css标签
            "{{ img %% }}" => '<!-- Snail: img Include --><img src="\1">', // 图片标签
            "{{ !%%! }}" => '<!-- Snail: \1 -->', // HTML单行注释
            "{{ %% (%%) }}" => '<?php \1(\2); ?>', // PHP函数标签
            "{{ file %% }}" => '<?php include "\1"; ?>', // 引入文件标签
            "{{ get_file %% }}" => '<?php echo file_get_contents("\1"); ?>', // 读取文件内容标签
            "{{ block %% }}" => '[[BLOCK_\1_START]]',
            "{{ /block }}" => '[[BLOCK_END]]',
        ],
    ],
];
