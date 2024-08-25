<?php
namespace Imccc\Snail\Integration\WeUI;

class Swiper
{
    private $slides = [];
    private $autoPlay;

    public function __construct($autoPlay = true)
    {
        $this->autoPlay = $autoPlay;
    }

    public function addSlide($imageUrl, $altText = '')
    {
        $this->slides[] = ['imageUrl' => $imageUrl, 'altText' => $altText];
    }

    public function render()
    {
        $html = '<div class="swiper-container">
                    <div class="swiper-wrapper">';

        foreach ($this->slides as $slide) {
            $html .= '<div class="swiper-slide"><img src="' . $slide['imageUrl'] . '" alt="' . $slide['altText'] . '"></div>';
        }

        $html .= '</div>';

        if (count($this->slides) > 1) {
            $html .= '<div class="swiper-pagination"></div>';
            $html .= '<div class="swiper-button-next"></div>';
            $html .= '<div class="swiper-button-prev"></div>';
        }

        $html .= '</div>';

        if ($this->autoPlay) {
            $html .= '
                <script>
                    var swiper = new Swiper(".swiper-container", {
                        loop: true,
                        autoplay: {
                            delay: 3000,
                        },
                        pagination: {
                            el: ".swiper-pagination",
                        },
                        navigation: {
                            nextEl: ".swiper-button-next",
                            prevEl: ".swiper-button-prev",
                        },
                    });
                </script>';
        }

        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\WeUISwiper;

// $swiper = new WeUISwiper();
// $swiper->addSlide(
//     'https://www.example.com/image1.jpg',    
//     'Image 1'
// );