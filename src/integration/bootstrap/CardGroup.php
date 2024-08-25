<?php
namespace Imccc\Snail\Integration\Bootstrap;

class CardGroup
{
    private $cards;

    public function __construct($cards = [])
    {
        $this->cards = $cards;
    }

    public function render()
    {
        $html = '<div class="card-group">';
        foreach ($this->cards as $card) {
            $html .= '<div class="card">';
            if (!empty($card['img'])) {
                $html .= '<img src="' . htmlspecialchars($card['img']) . '" class="card-img-top" alt="...">';
            }
            $html .= '<div class="card-body">';
            $html .= '<h5 class="card-title">' . htmlspecialchars($card['title']) . '</h5>';
            $html .= '<p class="card-text">' . htmlspecialchars($card['text']) . '</p>';
            $html .= '</div>';
            if (!empty($card['footer'])) {
                $html .= '<div class="card-footer">' . htmlspecialchars($card['footer']) . '</div>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// $cardGroup = new Imccc\Snail\Integration\Bootstrap\CardGroup([
//     ['title' => 'Card 1', 'text' => 'This is card 1', 'img' => 'path/to/image1.jpg'],
//     ['title' => 'Card 2', 'text' => 'This is card 2', 'img' => 'path/to/image2.jpg'],
//     ['title' => 'Card 3', 'text' => 'This is card 3', 'img' => 'path/to/image3.jpg']
// ]);
// echo $cardGroup->render();
