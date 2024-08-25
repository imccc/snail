<?php
namespace Imccc\Snail\Integration\Weui;

class SearchBar
{
    private $placeholder;
    private $name;
    private $onSearch;
    private $onCancel;

    public function __construct($placeholder = '搜索', $name = 'search', $onSearch = '', $onCancel = '')
    {
        $this->placeholder = $placeholder;
        $this->name = $name;
        $this->onSearch = $onSearch;
        $this->onCancel = $onCancel;
    }

    public function render()
    {
        return '
            <div class="weui-search-bar" id="searchBar">
                <form class="weui-search-bar__form" onsubmit="' . $this->onSearch . '">
                    <div class="weui-search-bar__box">
                        <i class="weui-icon-search"></i>
                        <input type="search" class="weui-search-bar__input" name="' . $this->name . '" id="searchInput" placeholder="' . $this->placeholder . '">
                        <a href="javascript:" class="weui-icon-clear" id="searchClear"></a>
                    </div>
                    <label class="weui-search-bar__label" id="searchText">
                        <i class="weui-icon-search"></i>
                        <span>' . $this->placeholder . '</span>
                    </label>
                </form>
                <a href="javascript:" class="weui-search-bar__cancel-btn" id="searchCancel" onclick="' . $this->onCancel . '">取消</a>
            </div>
            <script>
                document.getElementById("searchText").addEventListener("click", function() {
                    document.getElementById("searchBar").classList.add("weui-search-bar_focusing");
                    document.getElementById("searchInput").focus();
                });
                document.getElementById("searchClear").addEventListener("click", function() {
                    document.getElementById("searchInput").value = "";
                });
                document.getElementById("searchCancel").addEventListener("click", function() {
                    document.getElementById("searchBar").classList.remove("weui-search-bar_focusing");
                    document.getElementById("searchInput").value = "";
                });
            </script>';
    }
}
