<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Reader;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;

class ShopwareAttributeGroupReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    protected $count;
    public function read()
    {
        if($this->count === null) $this->count=0;
        if($this->count > 0) return null;
        echo "AttributeGroup...\n";
        $attribute = [];

        $attribute['label-en_US'] = 'Shopware attributes';
        $attribute['label-de_DE'] = 'Shopware Attribute';
        $attribute['code'] = 'shopware_attributes';
        $this->count++;
        return $attribute;
    }

    /**
     * @return mixed
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param mixed $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    public function getConfigurationFields()
    {
        return [

        ];
    }
}