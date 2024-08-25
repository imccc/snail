<?php

namespace Imccc\Snail\Interfaces;

interface MiddlewareInterface
{
    public function handle($next);
}
