<?php

namespace Imccc\Snail\Integration\Bootstrap;

/**
 * 分页类，用于生成分页导航HTML
 */
class Pagination
{
    // 总项数
    private $totalItems;
    // 每页项数
    private $itemsPerPage;
    // 当前页码
    private $currentPage;
    // URL模式
    private $urlPattern;

    /**
     * 构造函数，初始化分页参数
     *
     * @param int $totalItems 总项数
     * @param int $itemsPerPage 每页项数，默认为10
     * @param int $currentPage 当前页码，默认为1
     * @param string $urlPattern 页码URL模式，默认为'/page/(:num)'
     * @throws \InvalidArgumentException 如果参数类型不正确或值非法
     */
    public function __construct($totalItems, $itemsPerPage = 10, $currentPage = 1, $urlPattern = '/page/(:num)')
    {
        // 验证总项数
        if (!is_int($totalItems) || $totalItems < 0) {
            throw new \InvalidArgumentException('Total items must be a non-negative integer.');
        }
        // 验证每页项数
        if (!is_int($itemsPerPage) || $itemsPerPage <= 0) {
            throw new \InvalidArgumentException('Items per page must be a positive integer.');
        }
        // 验证当前页码
        if (!is_int($currentPage) || $currentPage <= 0) {
            throw new \InvalidArgumentException('Current page must be a positive integer.');
        }

        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = max(1, $currentPage); // 确保当前页至少为1
        $this->urlPattern = $urlPattern;
    }

    /**
     * 获取总页数
     *
     * @return int 总页数
     */
    public function getTotalPages()
    {
        return ceil($this->totalItems / $this->itemsPerPage);
    }

    /**
     * 获取当前页码
     *
     * @return int 当前页码
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * 获取每页项数
     *
     * @return int 每页项数
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * 获取指定页码的URL
     *
     * @param int $pageNum 页码
     * @return string 页码对应的URL
     */
    public function getPageUrl($pageNum)
    {
        return str_replace('(:num)', $pageNum, $this->urlPattern);
    }

    /**
     * 渲染分页导航HTML
     *
     * @return string 分页导航的HTML
     */
    public function render()
    {
        $totalPages = $this->getTotalPages();
        $currentPage = min($this->getCurrentPage(), $totalPages); // 确保当前页不超过总页数

        if ($totalPages <= 1) {
            return '';
        }

        $html = '<nav aria-label="Page navigation"><ul class="pagination">';

        // Previous button
        if ($currentPage > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->getPageUrl($currentPage - 1) . '" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link" aria-label="Previous"><span aria-hidden="true">&laquo;</span></span></li>';
        }

        // Page buttons
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $currentPage) {
                $html .= '<li class="page-item active" aria-current="page"><span class="page-link">' . $i . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . $this->getPageUrl($i) . '">' . $i . '</a></li>';
            }
        }

        // Next button
        if ($currentPage < $totalPages) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->getPageUrl($currentPage + 1) . '" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link" aria-label="Next"><span aria-hidden="true">&raquo;</span></span></li>';
        }

        $html .= '</ul></nav>';

        return $html;
    }
}