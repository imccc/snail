<?php

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Traits\DebugTrait;
use Imccc\Snail\Traits\HandleExceptionTrait;

class MailService
{
    use DebugTrait;
    // SMTP服务器主机
    private $host;

    // SMTP服务器端口
    private $port;

    // SMTP用户名
    private $username;

    // SMTP密码
    private $password;

    // 用于与SMTP服务器通信的套接字
    private $socket;

    // 连接超时时间
    private $connectionTimeout;

    // 响应超时时间
    private $responseTimeout;

    // 调试模式标志
    private $debug;

    // 日志记录标志
    private $log;

    // 日志文件名
    private $logprefix = ['mail', 'error'];

    // 依赖注入容器
    protected $container;

    // 日志记录器实例
    protected $logger;

    // 邮件服务配置数组
    protected $config;

    public function __construct(Container $container)
    {
        $this->container = $container;
        // 获取邮件配置
        $this->config = $this->container->resolve('ConfigService')->get('mail');
        // 获取日志记录器实例
        $this->logger = $this->container->resolve('LoggerService');
        // 初始化属性
        $this->host = $this->config['host'];
        $this->port = $this->config['port'];
        $this->username = $this->config['username'];
        $this->password = $this->config['password'];
        $this->connectionTimeout = $this->config['connectionTimeout'];
        $this->responseTimeout = $this->config['responseTimeout'];

        if (defined('SNAIL_DEBUG') && SNAIL_DEBUG['debug'] && SNAIL_DEBUG['mail'] ?? false) {
            register_shutdown_function([self::class, 'debug']);
        }
    }

    /**
     * 连接SMTP服务器
     */
    private function connect()
    {
        // 根据是否使用TLS建立套接字连接
        if ($this->config['tls']) {
            $this->socket = fsockopen("tls://" . $this->host, $this->port, $errno, $errstr, $this->connectionTimeout);
        } else {
            $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, $this->connectionTimeout);
        }
        // 记录连接调试信息
        self::bindDebugInfo('connect', "Connecting to SMTP host {$this->host}:{$this->port}");

        // 检查连接是否成功
        if (!$this->socket) {
            // 记录连接失败日志并抛出异常
            self::bindDebugInfo('connect', "Failed to connect to SMTP host {$this->host}:{$this->port} : $errstr");
        }
        // 设置响应超时时间
        stream_set_timeout($this->socket, $this->responseTimeout);
        $this->readResponse();
    }

    /**
     * 验证SMTP服务器的认证信息
     */
    private function authenticate()
    {
        // 发送EHLO命令
        $this->sendCommand("EHLO " . gethostname());
        // 发送AUTH LOGIN命令
        $this->sendCommand("AUTH LOGIN");
        // 发送用户名和密码进行Base64编码后的命令
        $this->sendCommand(base64_encode($this->username));
        $this->sendCommand(base64_encode($this->password));
    }

    /**
     * 发送SMTP命令
     * @param string $command 命令内容
     * @return string 响应内容
     */
    private function sendCommand($command)
    {
        fputs($this->socket, $command . "\r\n");
        return $this->readResponse();
    }

    /**
     * 读取SMTP服务器的响应
     * @return string 响应内容
     */
    private function readResponse()
    {
        $response = "";
        while ($str = fgets($this->socket, 4096)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ") {
                break;
            }
        }
        return $response;
    }

    /**
     * 发送邮件
     * @param string $from 发件人地址
     * @param string $to 收件人地址
     * @param string $subject 邮件主题
     * @param string $body 邮件正文
     * @param array|string|null $cc 抄送地址
     * @param array|string|null $bcc 密送地址
     * @return bool 发送成功返回true，失败返回false
     */
    public function sendMail($from, $to, $subject, $body, $cc = null, $bcc = null)
    {
        // 建立与SMTP服务器的连接
        $this->connect();
        // 进行SMTP身份验证
        $this->authenticate();

        // 发送邮件相关命令
        $this->sendCommand("MAIL FROM: <$from>");

        // 添加主收件人
        $this->sendCommand("RCPT TO: <$to>");

        // 添加抄送收件人
        if ($cc != null && is_array($cc)) {
            foreach ($cc as $ccRecipient) {
                $this->sendCommand("RCPT TO: <$ccRecipient>");
            }
        } else if ($cc != null && is_string($cc)) {
            $cc = explode(',', $cc);
            foreach ($cc as $ccRecipient) {
                $this->sendCommand("RCPT TO: <$ccRecipient>");
            }
        }

        // 添加密送收件人
        if ($bcc != null && is_array($bcc)) {
            foreach ($bcc as $bccRecipient) {
                $this->sendCommand("RCPT TO: <$bccRecipient>");
            }
        } else if ($bcc != null && is_string($bcc)) {
            $bcc = explode(',', $bcc);
            foreach ($bcc as $bccRecipient) {
                $this->sendCommand("RCPT TO: <$bccRecipient>");
            }
        }

        // 开始邮件内容
        $this->sendCommand("DATA");

        // 发送邮件头部
        $this->sendCommand("Subject: $subject\r\nTo: <$to>\r\nFrom: <$from>");

        // 添加抄送收件人到邮件头部
        foreach ($cc as $ccRecipient) {
            $this->sendCommand("Cc: <$ccRecipient>");
        }

        // 发送空行分隔头部和正文
        $this->sendCommand("");

        // 发送邮件正文
        $this->sendCommand($body);

        // 发送结束邮件内容的命令
        $this->sendCommand(".");

        // 发送QUIT命令结束SMTP会话
        $this->sendCommand("QUIT");

        // 记录邮件发送日志
        if ($this->log) {
            self::bindDebugInfo('mailSussess', "Mail sent successfully.\r\n From: $from\r\n To: $to\r\n CC: " . implode(", ", $cc) . "\r\n BCC: " . implode(", ", $bcc) . "\r\n Subject: $subject\r\n Body: $body");
        }

        // 关闭套接字连接
        fclose($this->socket);
    }

}
