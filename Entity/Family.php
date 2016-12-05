<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Entity;

use Pim\Bundle\CatalogBundle\Entity\Family as PimFamily;

/**
 * Overrides original Family entity to add
 * the Shopware propertyGroup ID.
 *
 * Class Family
 * @package Basecom\Bundle\ShopwareConnectorBundle\Entity
 */
class Family extends PimFamily
{
    /**
     * Shopware PropertyGroup ID
     *
     * @var integer
     */
    protected $swId;

    /**
     * @return integer
     */
    public function getSwId()
    {
        return $this->swId;
    }

    /**
     * @param integer $swId
     */
    public function setSwId($swId)
    {
        $this->swId = $swId;
    }
}
