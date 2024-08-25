<?php
namespace Imccc\Snail\Services;

class FileUploadService
{
    private $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    private $maxSize = 5 * 1024 * 1024; // 5MB

    public function handleUpload($file)
    {
        // 检查文件大小
        if ($file['size'] > $this->maxSize) {
            return ['success' => false, 'message' => 'File size exceeds the limit.'];
        }

        // 检查文件类型
        if (!in_array($file['type'], $this->allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type.'];
        }

        // 检查MIME类型和扩展名是否匹配
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!$this->checkFileType($file['tmp_name'], $ext)) {
            return ['success' => false, 'message' => 'File content does not match the file extension.'];
        }

        // 确保上传目录存在
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // 生成唯一文件名
        $uniqueFileName = uniqid() . '.' . $ext;
        $filePath = $uploadDir . $uniqueFileName;

        // 保存文件
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['success' => true, 'message' => 'File uploaded successfully.'];
        } else {
            // 记录更详细的错误信息
            error_log('Failed to move uploaded file: ' . $file['tmp_name'] . ' to ' . $filePath);
            return ['success' => false, 'message' => 'Failed to move uploaded file.'];
        }
    }

    private function checkFileType($filePath, $ext)
    {
        // 使用finfo_file检查MIME类型
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        // 通过扩展名检查
        $expectedMime = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
        ];

        return isset($expectedMime[$ext]) && $expectedMime[$ext] === $mimeType;
    }
}
