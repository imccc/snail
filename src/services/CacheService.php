<?php

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Config;
use Imccc\Snail\Core\Container;
use Imccc\Snail\Services\Drivers\FileCacheDriver;
use Imccc\Snail\Services\Drivers\MemcachedCacheDriver;
use Imccc\Snail\Services\Drivers\MongoCacheDriver;
use Imccc\Snail\Services\Drivers\RedisCacheDriver;
use RuntimeException;

class CacheService
{
    protected $container;
    protected $config;
    protected $expiration;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $this->container->resolve('ConfigService')->get('cache');
        $this->expiration = $this->config['expiration'];
    }

    public function get($key)
    {
        return $this->getCacheDriver()->get($key);
    }

    public function set($key, $value, $expiration = 0)
    {
        return $this->getCacheDriver()->set($key, $value, $this->expiration);
    }

    public function clear()
    {
        return $this->getCacheDriver()->clear();
    }

    protected function getCacheDriver()
    {
        $driver = $this->config['driver'];
        $driverKey = 'cache.' . $driver;
        switch ($driver) {
            case 'file':
                $this->container->bind($driverKey, function () {
                    return new FileCacheDriver($this->container);
                });
                break;
            case 'redis':
                $this->container->bind($driverKey, function () {
                    return new RedisCacheDriver($this->container);
                });
                break;
            case 'memcached':
                $this->container->bind($driverKey, function () {
                    return new MemcachedCacheDriver($this->container);
                });
                break;
            case 'mongodb':
                $this->container->bind($driverKey, function () {
                    return new MongoCacheDriver($this->container);
                });
                break;
            default:
                throw new RuntimeException('Unsupported cache driver');
        }

        return $this->container->resolve($driverKey);
    }
}
