<?php
namespace Imccc\Snail\Integration\WeUI;

class Article
{
    private $title;
    private $content;

    public function __construct($title = '', $content = [])
    {
        $this->title = $title;
        $this->content = $content;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function addParagraph($text)
    {
        $this->content[] = ['type' => 'paragraph', 'text' => $text];
    }

    public function addImage($src, $alt = '')
    {
        $this->content[] = ['type' => 'image', 'src' => $src, 'alt' => $alt];
    }

    public function addSubTitle($text)
    {
        $this->content[] = ['type' => 'subtitle', 'text' => $text];
    }

    public function render()
    {
        $html = '<div class="weui-article">';

        if ($this->title) {
            $html .= '<h1>' . $this->title . '</h1>';
        }

        foreach ($this->content as $element) {
            switch ($element['type']) {
                case 'paragraph':
                    $html .= '<p>' . $element['text'] . '</p>';
                    break;
                case 'image':
                    $html .= '<img src="' . $element['src'] . '" alt="' . $element['alt'] . '">';
                    break;
                case 'subtitle':
                    $html .= '<h2>' . $element['text'] . '</h2>';
                    break;
            }
        }

        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\Article;

// // 创建文章
// $article = new Article('这是文章标题');

// // 添加副标题
// $article->addSubTitle('这是副标题');

// // 添加段落
// $article->addParagraph('这是第一段内容。');
// $article->addParagraph('这是第二段内容。');

// // 添加图片
// $article->addImage('https://example.com/image1.jpg', '图片描述');

// // 添加更多段落
// $article->addParagraph('这是第三段内容。');
// $article->addParagraph('这是第四段内容。');

// // 渲染文章
// echo $article->render();
