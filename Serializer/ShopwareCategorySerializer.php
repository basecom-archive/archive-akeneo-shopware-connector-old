<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Serializer;

use Basecom\Bundle\ShopwareConnectorBundle\Entity\Category;

class ShopwareCategorySerializer
{
    public function serialize(Category $category)
    {
        echo "Serializer...\n";
        $category->setLocale('en_US');

//        $parent = null;
//        $name = null;
//        $sid = null;
//        $item = [];
//        $category->setLocale('en_US');
//        $name = $category->getLabel();
//        if($this->checkSid($category)){
//            $sid = $category->getSid();
//            $code = $category->getCode();
//            if($category->getParent() != null) {
//                $parent = $category->getParent()->getSid();
//            }
//            $item['parentId'] = $parent;
//            $item['name']     = $name;
//            $item['sid']      = $sid;
//            $item['code']     = $code;
//            echo "Serializer end...\n";
//            return $item;
//        } else {
//            return null;
//        }
    }

    /**
     * @param Category $category
     */
    protected function checkSid($category) {
        if($category->getSid() != null) return true;
        if($category->getParent() == null) {
            return false;
        } else {
            return $this->checkSid($category->getParent());
        }
    }
}