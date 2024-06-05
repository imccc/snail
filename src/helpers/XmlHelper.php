<?php

namespace Imccc\Snail\Helpers;

class SimpleXMLHelper extends SimpleXMLElement
{
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
