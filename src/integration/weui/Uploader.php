<?php
namespace Imccc\Snail\Integration\Weui;

class Uploader
{
    private $title;
    private $maxCount;
    private $files = [];
    private $fileInputName;
    private $accept;
    private $showPreview;

    public function __construct($title = '文件上传', $fileInputName = 'file', $accept = 'image/*', $maxCount = 9, $showPreview = true)
    {
        $this->title = $title;
        $this->fileInputName = $fileInputName;
        $this->accept = $accept;
        $this->maxCount = $maxCount;
        $this->showPreview = $showPreview;
    }

    public function addFile($url, $name)
    {
        $this->files[] = [
            'url' => $url,
            'name' => $name
        ];
        return $this;
    }

    public function setMaxCount($maxCount)
    {
        $this->maxCount = $maxCount;
        return $this;
    }

    public function setAccept($accept)
    {
        $this->accept = $accept;
        return $this;
    }

    public function render()
    {
        $html = '<div class="weui-uploader">';
        $html .= '<div class="weui-uploader__hd">';
        $html .= '<p class="weui-uploader__title">' . $this->title . '</p>';
        $html .= '<div class="weui-uploader__info">' . count($this->files) . '/' . $this->maxCount . '</div>';
        $html .= '</div>';
        $html .= '<div class="weui-uploader__bd">';
        if ($this->showPreview && !empty($this->files)) {
            $html .= '<ul class="weui-uploader__files">';
            foreach ($this->files as $file) {
                $html .= '<li class="weui-uploader__file" style="background-image:url(' . $file['url'] . ');"></li>';
            }
            $html .= '</ul>';
        }
        $html .= '<div class="weui-uploader__input-box">';
        $html .= '<input id="uploaderInput" class="weui-uploader__input" type="file" accept="' . $this->accept . '" multiple name="' . $this->fileInputName . '">';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\Weui\WeUIUploader;

// $uploader = new WeUIUploader('文件上传', 'file', 'image/*', 9, true);
// $uploader->addFile('https://www.baidu.com/img/bd_logo1.png', 'baidu.png');
// $uploader->addFile()