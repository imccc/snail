<?php

namespace Imccc\Snail\Helpers;

use SimpleXMLElement;

class SimpleXMLHelper extends SimpleXMLElement
{
    // XML 接口
    public function xmlEncode($result, $rootElement = 'root', $encoding = 'utf-8')
    {
        $xml = "<?xml version='1.0' encoding='" . $encoding . "' ?>\n";
        $xml .= "<{$rootElement}>\n";
        $xml .= $this->xmlToEncode($result);
        $xml .= "</{$rootElement}>\n";
        return $xml;
    }

    // XML 内容循环
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
            if (is_array($value) || is_object($value)) {
                $xml .= $this->xmlToEncode($value);
            } else {
                if ($this->needsCData($value)) {
                    $xml .= "<![CDATA[" . $value . "]]>";
                } else {
                    $xml .= $value;
                }
            }
            $xml .= "</$key>";
        }
        return $xml;
    }

    // 解析带有命名空间的 XML
    public function parseNamespaceXml($xmlstr)
    {
        $xmlstr = preg_replace('/\sxmlns="(.*?)"/', ' _xmlns="${1}"', $xmlstr);
        $xmlstr = preg_replace('/<(\/)?(\w+):(\w+)/', '<${1}${2}_${3}', $xmlstr);
        $xmlstr = preg_replace('/(\w+):(\w+)="(.*?)"/', '${1}_${2}="${3}"', $xmlstr);
        $xmlobj = simplexml_load_string($xmlstr);
        return json_decode(json_encode($xmlobj), true);
    }

    // 检查字符串是否需要 CDATA 包裹
    public function needsCData($string): bool
    {
        if (!is_string($string)) {
            return false; // 如果不是字符串，则不需要 CDATA
        }
        // 检查是否包含 XML 特殊字符
        return preg_match('/[\<\>&\'"]/', $string) === 1;
    }

    // 添加 CDATA 节点
    public function addChildWithCData($name, $value)
    {
        $newChild = $this->addChild($name);
        $node = dom_import_simplexml($newChild);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($value));
        return $newChild;
    }
}
