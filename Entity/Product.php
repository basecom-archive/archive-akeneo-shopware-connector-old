<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Entity;

use Pim\Component\Catalog\Model\Product as PimProduct;

/**
 * Class Product
 *
 * @package Basecom\Bundle\ShopwareConnectorBundle\Entity
 */
class Product extends PimProduct
{
    /**
     * Identifier if product should be able to send variants to Shopware
     *
     * @var boolean
     */
    protected $isVariant = false;

    /** @var integer */
    protected $swProductId;

    /**
     * @return boolean
     */
    public function isVariant()
    {
        return $this->isVariant;
    }

    /**
     * @param boolean $isVariant
     */
    public function setIsVariant($isVariant)
    {
        $this->isVariant = $isVariant;
    }

    /**
     * @return integer
     */
    public function getSwProductId()
    {
        return $this->swProductId;
    }

    /**
     * @param integer $swProductId
     */
    public function setSwProductId($swProductId)
    {
        $this->swProductId = $swProductId;
    }
}
