<?php
namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Container;

class Service
{
    protected $container; // 服务容器
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

}