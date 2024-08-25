<?php

namespace Imccc\Snail\Services\Drivers;

use Imccc\Snail\Core\Container;
use MongoDB\Client;

class MongoCacheDriver
{
    protected $collection;
    protected $config;
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $this->container->resolve('ConfigService')->get('cache.driverConfig.mongo');
        $username = $this->config['username'];
        $password = $this->config['password'];
        $host = $this->config['host'];
        $port = $this->config['port'];
        $database = $this->config['db'];
        $collection = $this->config['collection'];

        $dsn = "mongodb://$username:$password@$host:$port/$database";

        $client = new Client($dsn);
        $this->collection = $client->selectDatabase($database)->selectCollection($collection);
    }

    public function get($key)
    {
        $document = $this->collection->findOne(['_id' => $key]);
        return $document ? $document['value'] : null;
    }

    public function set($key, $value, $expiration = 0)
    {
        $this->collection->updateOne(['_id' => $key], ['$set' => ['value' => $value]], ['upsert' => true]);
        return true;
    }

    public function delete($key)
    {
        $this->collection->deleteOne(['_id' => $key]);
        return true;
    }

    public function clear()
    {
        $this->collection->deleteMany([]);
        return true;
    }
}
