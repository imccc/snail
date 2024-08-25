<?php

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Config;
use Imccc\Snail\Core\Container;
use Imccc\Snail\Services\Drivers\FileCacheDriver;
use Imccc\Snail\Services\Drivers\MemcachedCacheDriver;
use Imccc\Snail\Services\Drivers\MongoCacheDriver;
use Imccc\Snail\Services\Drivers\RedisCacheDriver;
use Imccc\Snail\Services\Service;
use RuntimeException;

class CacheService extends Service
{
    protected $cfg;
    protected $expiration;
    protected $driver;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->cfg = $this->config->get('cache');
        $this->expiration = $this->cfg['expiration'];
        $this->driver = $this->createCacheDriver();
    }

    public function get(string $key): mixed
    {
        return $this->driver->get($key);
    }

    public function set(string $key, mixed $value, int $expiration = 0): bool
    {
        return $this->driver->set($key, $value, $expiration ?: $this->expiration);
    }

    public function clear(): bool
    {
        return $this->driver->clear();
    }

    private function createCacheDriver()
    {
        $driver = $this->cfg['driver'];
        $driverKey = 'cache.' . $driver;

        if (!$this->container->has($driverKey)) {
            $this->bindCacheDriver($driver, $driverKey);
        }

        return $this->container->resolve($driverKey);
    }

    private function bindCacheDriver(string $driver, string $driverKey)
    {
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
