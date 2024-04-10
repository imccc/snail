<?php
namespace Imccc\Snail\Mvc;

use Imccc\Snail\Core\Api;
use Imccc\Snail\Core\Container;
use RuntimeException;

class Controller
{
    protected $api;
    protected $config; // 配置信息
    protected $routes; // 用于存储路由信息
    protected $container;
    protected $logger;
    protected $logprefix = ['controller', 'error'];
    protected $_data = [];
    private $_view;
    private $_model;
    private $_method;
    private $_params;
    private $_action;
    private $_controller;
    private $_module;
    private $_debuginfo = [];

    /**
     * 构造函数
     *
     * @param array $routes 路由数组
     */
    public function __construct($routes)
    {
        $this->_debuginfo = [
            'module' => $this->routes['module'],
            'controller' => $this->routes['controller'],
            'action' => $this->routes['action'],
            'params' => $this->routes['params'],
        ];
        $this->routes = $routes;
        
        $this->container = Container::getInstance();
        $this->config = $this->container->resolve('ConfigService');
        $this->logger = $this->container->resolve('LoggerService');
        $this->api = $this->container->resolve('ApiService');

        $this->_view = new View($this->container);
        $this->_model = new Model($this->container);
    }

    /**
     * 注册服务
     *
     * @param string $name 服务名称
     * @param mixed $service 服务对象
     */
    public function registerService(string $name, $service): void
    {
        $this->container->bind($name, $service);
    }

    /**
     * 分配数据给视图
     *
     * @param string|array $key 参数键名或参数数组
     * @param mixed $value 参数值（仅在第一个参数为键名时有效）
     */
    public function assign($key, $value = null): void
    {
        $this->_view->assign($key, $value);
    }

    /**
     * 动态展示视图，不生成缓存
     *
     * @param string $tpl 视图文件路径
     * @return string 视图内容
     */
    public function display(string $tpl = null): string
    {
        $this->_view->setData($this->_data);
        return $this->_view->render($tpl);
    }

    /**
     * 使用模版渲染视图,生成静态缓存
     */
    public function cache($tpl = null)
    {
        $this->_view->cache($tpl);
        return $this;
    }

    /**
     * 输出API数据
     */
    public function api($data = null)
    {
        $this->_api = new Api($this->container);
        $this->_api->show($data);
        return $this;
    }

    /**
     * 设置请求方法
     *
     * @param string $method 请求方法
     */
    public function setRequestMethod(string $method): void
    {
        $this->_method = strtoupper($method);
    }

    /**
     * 限制请求方法
     *
     * @param array|string $allowedMethods 允许的请求方法数组或以逗号分隔的字符串
     * @return bool 返回是否请求方法在允许的范围内
     */
    public function inspect($allowedMethods): bool
    {
        $this->_method = $this->setRequestMethod($_SERVER['REQUEST_METHOD']) ?? ''; // 获取请求方法
        $allowedMethods = is_array($allowedMethods) ? $allowedMethods : explode(',', $allowedMethods);
        return in_array($this->_method, array_map('strtoupper', $allowedMethods));
    }

    /**
     * 创建模型
     *
     * @param string $model 模型类名
     * @return Model 模型对象
     */
    public function setModel(string $model): Model
    {
        return $this->_model->setModel($model);
    }

    /**
     * 根据点分隔的键名读取请求参数
     *
     * @param string $ps 点分隔的参数键名
     * @return mixed 参数值或者null
     */
    public function input(string $ps = ''): ?mixed
    {
        if (empty($ps)) {
            return $this->routes;
        }

        $keys = explode('.', $ps);
        $value = $this->routes;

        // 按点分隔的键名逐层查找
        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                return null; // 没找到指定的键名时返回null
            }
        }

        $this->logger->log("获取参数： $ps  值: $value", $this->logprefix[0]);
        return $value;
    }

    /**
     * 获取POST请求体中的数据
     *
     * 根据Content-Type处理不同格式的请求体
     *
     * @return mixed 解析后的数据
     * @throws RuntimeException 解析错误时抛出异常
     */
    public function getPost(): mixed
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [];
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $rawData = file_get_contents('php://input');

        switch (true) {
            case strpos($contentType, 'application/json') !== false:
                $data = json_decode($rawData, true);
                break;
            case strpos($contentType, 'application/x-www-form-urlencoded') !== false:
                parse_str($rawData, $data);
                break;
            case strpos($contentType, 'application/xml') !== false:
                $data = simplexml_load_string($rawData);
                if ($data === false) {
                    $this->logger->log('XML 解析错误', $this->logprefix[1]);
                    throw new RuntimeException('XML 解析错误');
                }
                $data = (array) $data;
                break;
            default:
                $data = [];
        }

        if ($data === null) {
            $this->logger->log('请求体解析错误', $this->logprefix[1]);
            throw new RuntimeException('请求体解析错误');
        }
        // 日志
        $this->logger->log('获取POST请求体\r\n' . $data, $this->logprefix[0]);
        $this->debuginfo['post_data'] = $data;
        return $data;
    }

    /**
     * 获取所有HTTP请求头信息
     *
     * @return array 包含所有请求头信息的关联数组
     */
    public function getallheaders(): array
    {
        $headers = [];
        // 遍历$_SERVER数组，提取HTTP头信息
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$headerName] = $value;
            }
        }
        $this->logger->log('获取所有HTTP请求头信息\r\n' . $headers, $this->logprefix[0]);
        return $headers;
    }
}
