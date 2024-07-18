<?php

namespace Imccc\Snail\Services\Drivers;

use Imccc\Snail\Core\Container;
use Redis;

class RedisCacheDriver
{
    protected $redis;
    protected $config;
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $this->container->resolve('ConfigService')->get('cache.driverConfig.redis');
        $this->redis = new Redis();
        $this->redis->connect($this->config['host'], $this->config['port']);

        if (!empty($this->config['password'])) {
            $this->redis->auth($this->config['password']);
        }
        if (!empty($this->config['database'])) {
            $this->redis->select($this->config['database']);
        }
    }

    public function get($key)
    {
        return $this->redis->get($key);
    }

    public function set($key, $value, $expiration = 0)
    {
        if ($expiration > 0) {
            return $this->redis->setex($key, $expiration, $value);
        } else {
            return $this->redis->set($key, $value);
        }
    }

    public function delete($key)
    {
        return $this->redis->del($key);
    }

    public function clear()
    {
        return $this->redis->flushDB();
    }
}
