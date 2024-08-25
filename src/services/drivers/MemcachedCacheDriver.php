<?php

namespace Imccc\Snail\Services\Drivers;

use Imccc\Snail\Core\Container;
use Memcached;

class MemcachedCacheDriver
{
    protected $memcached;
    protected $config;
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $this->container->resolve('ConfigService')->get('cache.driverConfig.memcached');
        $this->memcached = new Memcached();
        $this->memcached->addServer($this->config['host'], $this->config['port']);
    }

    public function get($key)
    {
        return $this->memcached->get($key);
    }

    public function set($key, $value, $expiration = 0)
    {
        return $this->memcached->set($key, $value, $expiration);
    }

    public function delete($key)
    {
        return $this->memcached->delete($key);
    }

    public function clear()
    {
        return $this->memcached->flush();
    }
}
