<?php
namespace Imccc\Snail\Mvc;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Interfaces\ControllerInterface;
use Imccc\Snail\Mvc\Model;
use Imccc\Snail\Mvc\View;

class Controller implements ControllerInterface
{
    protected $api;
    protected $config;
    protected $conf;
    protected $routes;
    protected $container;
    protected $logger;
    protected $logprefix = ['controller', 'error', 'debug'];
    protected $_debuginfo;
    protected $_data = [];
    private $_view;
    private $_model;
    private $_tpl;
    private $_input;
    private $_post;
    private $_permission;
    private $_action;
    private $_api;

    public function __construct($routes)
    {
        $this->routes = $routes;

        $this->_debuginfo = [
            'namespace' => $this->routes['namespace'],
            'controller' => $this->routes['controller'],
            'action' => $this->routes['action'],
            'params' => $this->routes['params'],
        ];

        $this->container = Container::getInstance();
        $this->logger = $this->container->resolve('LoggerService');
        $this->config = $this->container->resolve('ConfigService');
        $this->conf = $this->config->get('logger.on');

        if (DEBUG['controller'] && DEBUG['debug']) {
            register_shutdown_function([$this, 'debug']);
        }

    }

    /**
     * 生成API方法
     * @return void
     */
    public function api()
    {
        if (!$this->_api) {
            $this->_api = $this->container->resolve('ApiService');
        }
        return $this->_api;
    }

    /**
     * 获取与控制器关联的模型实例。
     *
     * @return mixeds
     */
    public function getModel()
    {
        if (!$this->_model) {
            $this->_model = new Model($this->container);
        }
        return $this->_model;

    }

    /**
     * 获取与控制器关联的视图实例。
     *
     * @return mixed
     */
    public function getView()
    {
        if (!$this->_view) {
            $this->_view = new View($this->container);
        }
        return $this->_view;

    }

    /**
     * 显示视图
     *
     * @param string $tpl 模版信息，可以为空
     * @return void
     */
    public function display($tpl = '')
    {
        if (!empty($tpl)) {
            $this->_tpl = $tpl;
        }
        // 根据视图模板和数据渲染视图，并返回渲染结果
        return $this->_view->render($this->_tpl, $this->_data);
    }

    /**
     * 将数据分配到视图。
     *
     * @param string $key 数据键
     * @param mixed $value 数据值
     * @return void
     */
    public function assign(string $key, $value): void
    {
        $this->_view->assign($key, $value);
    }

    /**
     * 处理输入参数。
     *
     * @param string|null $param 参数名称，为空时返回所有输入参数
     * @return mixed
     */
    public function input(string $param = null)
    {
        // 获取所有输入参数并进行过滤
        $input = $this->sanitizeInput(array_merge($this->routes, $_POST, $_FILES, $_COOKIE, $_SESSION));

        // 添加请求头信息到输入参数中
        $input['headers'] = $this->getallheaders();

        // 添加请求体数据到输入参数中
        $rawData = file_get_contents('php://input');

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        switch (true) {
            case strpos($contentType, 'application/json') !== false:
                $input['body'] = json_decode($rawData, true);
                break;
            case strpos($contentType, 'application/x-www-form-urlencoded') !== false:
                parse_str($rawData, $input['body']);
                break;
            case strpos($contentType, 'application/xml') !== false:
                $data = simplexml_load_string($rawData);
                if ($data === false) {
                    $this->logger->log('XML 解析错误', $this->logprefix[1]);
                    throw new RuntimeException('XML 解析错误');
                }
                $input['body'] = (array) $data;
                break;
            default:
                $input['body'] = [];
        }

        // 如果未指定参数名称，则返回所有输入参数
        if ($param === null) {
            return $input;
        }

        // 按点分隔的参数名称
        $keys = explode('.', $param);

        // 逐层查找参数值
        $value = $input;
        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                return null; // 参数不存在时返回 null
            }
        }

        return $value;
    }

    /**
     * 综合过滤输入数据
     *
     * @param mixed $input 输入数据
     * @return mixed 过滤后的数据
     */
    public function sanitizeInput($input)
    {
        // 如果输入数据是数组，则递归对数组元素进行过滤
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }

        // 如果输入数据是字符串，则进行 HTML 转义和去除多余空白字符处理
        if (is_string($input)) {
            $input = trim($input); // 去除首尾空白字符
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8'); // HTML 转义
        }

        // 返回过滤后的数据
        return $input;
    }

    /**
     * 限制请求方法
     *
     * @param array|string $allowedMethods 允许的请求方法数组或以逗号分隔的字符串
     * @return bool 返回是否请求方法在允许的范围内
     */
    protected function inspect($allowedMethods): bool
    {
        $this->_method = $this->setRequestMethod($_SERVER['REQUEST_METHOD']) ?? ''; // 获取请求方法
        $allowedMethods = is_array($allowedMethods) ? $allowedMethods : explode(',', $allowedMethods);
        return in_array($this->_method, array_map('strtoupper', $allowedMethods));
    }

    /**
     * 获取所有HTTP请求头信息
     *
     * @return array 包含所有请求头信息的关联数组
     */
    protected function getallheaders(): array
    {
        $headers = [];
        // 遍历$_SERVER数组，提取HTTP头信息
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$headerName] = $value;
            }
        }
        $this->_debuginfo['headers'] = $headers;
        return $headers;
    }

    /**
     * 添加调试信息。
     *
     * @return void
     */
    public function debug(): void
    {
        echo "<h3>以下信息由 类: " . self::class . " 提供<small>@ " . date("Y-m-d H:i:s.u") . "</small></h3>";
        echo '<pre>';
        print_r($this->_debuginfo);
        echo '</pre>';
    }

    /**
     * 检查权限
     * @param string $action 要执行的操作
     */
    public function checkPermission(string $action): bool
    {
        return true;
    }

}
