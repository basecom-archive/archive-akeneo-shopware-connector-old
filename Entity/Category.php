<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Entity;

use Pim\Bundle\CatalogBundle\Entity\Category as PimCategory;

/**
 * Overrides original Category entity to add
 * the Shopware category ID
 *
 * Class Category
 * @package Basecom\Bundle\ShopwareConnectorBundle\Entity
 */
class Category extends PimCategory
{
    /**
     * Shopware Category ID
     *
     * @var integer
     */
    protected $swId;

    /**
     * @return int
     */
    public function getSwId()
    {
        return $this->swId;
    }

    /**
     * @param int $swId
     */
    public function setSwId($swId)
    {
        $this->swId = $swId;
    }
}
