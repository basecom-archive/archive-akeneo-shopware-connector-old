<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Serializer;

use Basecom\Bundle\ShopwareConnectorBundle\Entity\Family;

class ShopwareFamilySerializer
{
    public function serialize(Family $family, $locale)
    {
        $name = $family->getLabel();
        echo $name." id: ".(int)$family->getSid()."\n";
        $item = array(
            'id'        => $family->getSid(),
            'data'      => array(
                'name'      => $name,
                'position'  => 0,
                'comparable'=> false,
                'sortMode'  => 0
            )
        );
        return $item;
    }
}