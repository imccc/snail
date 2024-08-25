<?php

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Container;

class QueueService
{
    // 缓存服务实例
    private $cacheService;

    // 依赖注入容器
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        // 获取缓存服务实例
        $this->cacheService = $this->container->resolve('CacheService');
    }

    /**
     * 将任务添加到队列中
     * @param array $taskData 任务数据
     */
    public function addTask($taskData)
    {
        // 将任务数据存储到缓存队列中
        $this->cacheService->set('queue', $taskData);
    }

    /**
     * 删除已执行的任务
     */
    private function deleteTask()
    {
        // 从缓存队列中删除已执行的任务
        $this->cacheService->clear('queue');
    }

    /**
     * 从队列中取出任务并执行
     */
    public function processQueue()
    {
        // 循环监听队列，处理任务
        while (true) {
            // 从缓存队列中取出任务数据
            $taskData = $this->cacheService->get('queue');

            // 如果有任务数据，则执行相应的处理逻辑
            if (!empty($taskData)) {
                $this->processTask($taskData);
                // 执行完成后删除任务
                $this->deleteTask();

            } else {
                // 如果队列为空，等待一段时间后再次检查
                sleep(1);
            }
        }
    }

    /**
     * 处理任务
     * @param array $taskData 任务数据
     */
    private function processTask($taskData)
    {
        // 根据任务类型执行相应的处理逻辑
        switch ($taskData['task']) {
            case 'send_email':
                // 处理发送邮件任务逻辑
                $this->sendEmail($taskData['to'], $taskData['subject'], $taskData['body']);
                break;
            case 'process_image':
                // 处理处理图片任务逻辑
                $this->processImage($taskData['image_path']);
                break;
            // 添加其他任务类型的处理逻辑
            // case 'other_task':
            //     // 处理其他任务的逻辑
            //     break;
            default:
                // 未知任务类型
                echo "Unknown task: " . $taskData['task'] . PHP_EOL;
                break;
        }
    }

    /**
     * 发送邮件
     * @param string $to 收件人邮箱
     * @param string $subject 邮件主题
     * @param string $body 邮件内容
     */
    private function sendEmail($to, $subject, $body)
    {
        // 使用邮件服务发送邮件
        $mailService = $this->container->resolve('MailService');
        $mailService->sendMail('sender@example.com', $to, $subject, $body);
    }

    /**
     * 处理图片
     * @param string $imagePath 图片路径
     */
    private function processImage($imagePath)
    {
        // 处理图片的逻辑
        // 例如：压缩、裁剪、添加水印等操作
        echo "Processing image: $imagePath" . PHP_EOL;
    }
}
