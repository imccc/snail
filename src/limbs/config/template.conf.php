<?php
return [
    'engine' => 'snail', // 引擎名称 snail twig
    'cache' => false, // 是否开启缓存
    'path' => '', // 模板路径,不指定则默认在当前目录下,dirname($_SERVER['DOCUMENT_ROOT']) . '/app/views/tpl'
    'default' => 'index',
    'ext' => '.tpl',
    'tags' => [
        "if %%" => '<?php if(\1):?>', //if标签
        "else" => '<?php else:?>', //else标签
        "else if %%" => '<?php elseif(\1):?>', //else if标签
        "elseif %%" => '<?php elseif(\1):?>', //else if标签w
        "/if" => '<?php endif;?>', //endif标签
        "foreach %%" => '<?php foreach(\1):?>', //foreach标签
        "/foreach" => '<?php endforeach;?>', //endforeach标签
        "for %%" => '<?php for(\1):?>', //for标签
        "/for" => '<?php endfor;?>', //endfor标签
        "while %%" => '<?php while(\1):?>', //while标签
        "/while" => '<?php endwhile;?>', //endwhile标签
        "continue" => '<?php continue;?>', //continue标签
        "break" => '<?php break;?>', //break标签
        "upper %%" => '<?php echo strtoupper(\1); ?>', //大写标签
        "lower %%" => '<?php echo strtolower(\1); ?>', //小写标签
        "html %%" => '<?php echo htmlspecialchars(\1); ?>', //html标签
        "html_decode %%" => '<?php echo htmlspecialchars_decode(\1); ?>', //html_decode标签
        "html_entity_decode %%" => '<?php echo html_entity_decode(\1); ?>', //html_entity_decode标签
        "html_entity_encode %%" => '<?php echo html_entity_encode(\1); ?>', //html_entity_encode标签
        "nl2br %%" => '<?php echo nl2br(\1); ?>', //nl2br标签
        "trim %%" => '<?php echo trim(\1); ?>', //trim标签
        "trim_left %%" => '<?php echo ltrim(\1); ?>', //trim_left标签
        "trim_right %%" => '<?php echo rtrim(\1); ?>', //trim_right标签
        "trim_all %%" => '<?php echo trim(\1," \t\n\r\0\x0B"); ?>', //trim_all标签
        "trim_all_left %%" => '<?php echo ltrim(\1," \t\n\r\0\x0B"); ?>', //trim_all_left标签
        "trim_all_right %%" => '<?php echo rtrim(\1," \t\n\r\0\x0B"); ?>', //trim_all_right标签

        " $%% " => '<?php $\1;?>',
        "$%%" => '<?php echo $\1;?>',

        " %% = %% " => '<?php \1 = \2;?>', //$1 = $2
        "var %%=%%" => '<?php \1 = \2;?>', //$1 = $2

        " $%% = $%% " => '<?php $\1 = $\2;?>',
        "var $%%=$%%" => '<?php $\1 = $\2;?>',

        " $%%++ " => '<?php $\1++;?>', //X++
        "int $%%++" => '<?php $\1++;?>', //X++

        " $%%-- " => '<?php $\1--;?>', //X--
        "int $%%--" => '<?php $\1--;?>', //X--

        "die" => '<?php die;?>', //die
        "exit" => '<?php exit;?>', //exit

        "/*" => '<?php /** Snail: ' . PHP_EOL, //php代码内注释
        "*/" => '*/ ?>', //注释关闭

        "!" => PHP_EOL . '<!-- Snail: ', //Html代码内注释
        "/!" => ' -->' . PHP_EOL, //注释关闭

        "php" => '<?php ', //整段php代码
        "/php" => '?>', //代码半闭

        "js" => '<!-- Snail: Js Tag Start --> ' . PHP_EOL . '<script>', //整段js代码
        "/js" => '</script>' . PHP_EOL . '<!-- Snail: Js Tag End -->', //代码半闭

        "css" => '<!-- Snail: Style Tag Start -->' . PHP_EOL . '<style>', //整段CSS样式代码
        "/css" => '</style>' . PHP_EOL . '<!-- Snail: Style Tag End -->', //代码半闭

        "js %%" => '<!-- Snail: JS Include --><script src=\1></script>', //js标签
        "css %%" => '<!-- Snail: CSS Link --><link href=\1 rel="stylesheet">', //css标签
        "img %%" => '<!-- Snail: img Include--><img src=\1>', //图片标签
        "!%%!" => '<!-- Snail: $1 -->', //html单行注释

        "{%%(%%)}" => '<?php \1(\2);?>', //PHP函数标签 {var_dump($a)}等 本标签不能放在其它前面执行
        "%%(%%)" => '<?php echo \1(\2);?>', //PHP函数标签 {var_dump($a)}等 本标签不能放在其它前面执行

        "include_file %%" => '<?php include "\1";?>', //引入文件
        "get_file %%" => '<?php file_get_contents(\1);?>', //读取文件内容
    ],
];
