<?php

namespace Imccc\Snail\Interfaces;

interface ViewInterface
{

    /**
     * 显示视图
     * @param string $tpl
     * @return string
     */
    public function display($tpl = null);

   
}
