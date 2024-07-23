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

    protected $action;
    protected $params;
    protected $routes;

    protected $container;
    protected $logger;
    protected $logprefix = ['controller', 'error', 'debug'];
    protected $_data = [];
    private $_view;
    private $_model;
    private $_tplpath;
    private $_tpl;
    private $_input;
    private $_post;
    private $_permission;
    private $_action;
    private $_api;

    public function __construct($action, $params, $routes)
    {
        // 注册全局异常处理函数
        $this->routes = $routes;
        $this->params = $params;
        $this->action = $action;
        $this->container = Container::getInstance();
        $this->logger = $this->container->resolve('LoggerService');
        $this->config = $this->container->resolve('ConfigService');
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
     * 生成API方法
     * @return void
     */
    public function getApi()
    {
        if (!$this->_api) {
            $this->_api = $this->container->resolve('ApiService');
        }
        return $this->_api;
    }

    /**
     * 显示API
     *
     * @param [type] $oridata
     * @return void
     */
    public function api($oridata)
    {
        $this->getApi();
        $this->_api->show($oridata);
    }

    /**
     * 显示视图
     *
     * @param string $tpl 模版信息，可以为空
     * @return void
     */
    public function display($tpl = '')
    {
        // Debug::dump($this->routes,true,false,true);
        if ($tpl == '' || $tpl == null) {
            $tpl = $this->routes['namespace'] . "\\" . $this->routes['controller'] . '\\' . $this->routes['action'];
        }
        // echo $tpl;die;
        $this->getView();
        $this->assign([
            'data' => $this->_data,
            'routes' => $this->routes,
            'title' => 'Snail PHP',
            'tplpath' => $this->preParseTpl($tpl),
        ]);
        $this->_view->display();
    }

    /**
     * 根据请求处理数据，如果是ajax请求则返回api数据，否则显示视图
     * @param [type] $data
     * @return void
     */
    public function fetch($data)
    {
        if ($this->isAjax()) {
            $this->api($data);
        } else {
            $this->display();
        }
    }

    /**0
     * 预先解析视图模板位置
     * @return void
     */
    public function preParseTpl($tpl)
    {
        $pathFormat = $this->config->get('template.path');
        $group = $this->routes['group'];
        $controller = $this->routes['controller'];
        if (!empty($tpl)) {
            $methodName = $tpl;
        } else {
            $methodName = $this->routes['action'];
        }
        $path = str_replace(['{$group}', '{$controller}'], [$group, $controller], $pathFormat);
        $this->logger->log(self::class . ' preParseTplPath : ' . $path, $this->logprefix[2]);
        return $path;
    }

    /**
     * 将数据分配到视图。
     *
     * @param string $key 数据键
     * @param mixed $value 数据值
     * @return void
     */
    public function assign($key, $value = null): void
    {
        $this->getView();
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
                    throw new \Exception('XML format error');
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
        $this->logger->log('Request Headers: ' . json_encode($headers), $this->logprefix[2]);
        return $headers;
    }

    /**
     * 检查权限
     * @param string $action 要执行的操作
     */
    public function checkPermission(string $action): bool
    {
        return true;
    }

    /**
     * 处理 AJAX 请求
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

}
