<?php

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Container;

class SocketClientService
{
    protected $config;
    protected $logconf;
    protected $container;
    protected $logger;
    protected $logfile = '_SOCKET_CLIENT_';
    protected $address;
    protected $port;
    protected $socket;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $this->container->resolve('ConfigService');
        $this->logger = $this->container->resolve('LoggerService');
        $this->logconf = $this->config->get('logger.on');
        $this->address = $this->config->get('socket_server.address');
        $this->port = $this->config->get('socket_server.port');
    }

    public function connect()
    {
        // 创建一个 TCP/IP socket
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if (!$this->socket) {
            if ($this->logconf['socket']) {
                $this->logger->log("创建客户端 Socket 失败：" . socket_strerror(socket_last_error()), $this->logfile);
            }
            echo "创建客户端 Socket 失败：" . socket_strerror(socket_last_error()) . PHP_EOL;
            return false;
        }

        // 连接服务器
        $result = socket_connect($this->socket, $this->address, $this->port);

        if (!$result) {
            if ($this->logconf['socket']) {
                $this->logger->log("连接服务器失败：" . socket_strerror(socket_last_error()), $this->logfile);
            }
            echo "连接服务器失败：" . socket_strerror(socket_last_error()) . PHP_EOL;
            return false;
        }

        echo "连接服务器成功" . PHP_EOL;

        return true;
    }

    public function send($message)
    {
        // 向服务器发送消息
        socket_write($this->socket, $message, strlen($message));

        echo "发送消息给服务器：" . $message . PHP_EOL;

        // 读取服务器响应
        $response = socket_read($this->socket, 1024);

        echo "收到服务器响应：" . $response . PHP_EOL;

        return $response;
    }

    public function close()
    {
        // 关闭 socket 连接
        socket_close($this->socket);
        echo "关闭客户端连接" . PHP_EOL;
    }
}
