<?php

namespace Imccc\Snail\Helpers;

use SimpleXMLElement;

class SimpleXMLHelper extends SimpleXMLElement
{

    //xml接口
    public function xmlEncode($result, $rootElement = 'root', $encoding = 'utf-8')
    {
        $xml = "<?xml version='1.0' encoding='" . $encoding . "' ?>\n";
        $xml .= "<{$rootElement}>\n";
        $xml .= $this->xmlToEncode($result);
        $xml .= "</{$rootElement}>\n";
        return $xml;
    }

    //xml内容循环
    public function xmlToEncode($data)
    {
        if (empty($data)) {
            return '';
        }
        $xml = $attr = '';
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $attr = " id='{$key}'";
                $key = "item";
            }
            $xml .= "<{$key}{$attr}>";
            if ($this->needsCData($value)) {
                $xml .= is_array($value) || is_object($value) ? $this->xmlToEncode($value) : "<![CDATA[" . $value . "]]>";
            } else {
                $xml .= is_array($value) || is_object($value) ? self::xmlToEncode($value) : $value;
            }
            $xml .= "</$key>";
        }
        return $xml;
    }

    // 解析带有命名空间的xml.
    public function parseNamespaceXml($xmlstr)
    {
        $xmlstr = preg_replace('/\sxmlns="(.*?)"/', ' _xmlns="${1}"', $xmlstr);
        $xmlstr = preg_replace('/<(\/)?(\w+):(\w+)/', '<${1}${2}_${3}', $xmlstr);
        $xmlstr = preg_replace('/(\w+):(\w+)="(.*?)"/', '${1}_${2}="${3}"', $xmlstr);
        $xmlobj = simplexml_load_string($xmlstr);
        return json_decode(json_encode($xmlobj), true);
    }

    /**
     * 检查字符串是否需要 CDATA 包裹
     *
     * @param string $string 要检查的字符串
     * @return bool 是否需要 CDATA 包裹
     */
    public function needsCData($string): bool
    {
        // 检查是否包含 XML 特殊字符
        if (preg_match('/[\<\>&\'"]/', $string)) {
            return true;
        }
        return false;
    }

    /**
     * 添加 CDATA 节点
     *
     * @param string $name 节点名称
     * @param string $value 节点值
     * @return SimpleXMLElement 新创建的节点
     */
    public function addChildWithCData($name, $value)
    {
        $newChild = $this->addChild($name);
        $node = dom_import_simplexml($newChild);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($value));
        return $newChild;
    }
}
