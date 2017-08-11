<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Entity;

use Pim\Bundle\CatalogBundle\Entity\Category as PimCategory;

/**
 * @author  Amir El Sayed <elsayed@basecom.de>
 *
 * Overrides original Category entity to add
 * the Shopware category ID.
 *
 * Class Category
 * @package Basecom\Bundle\ShopwareConnectorBundle\Entity
 */
class Category extends PimCategory
{
    /**
     * Shopware Category ID
     *
     * @var array
     */
    protected $swId;

    /**
     * @return array
     */
    public function getSwId($locale)
    {
        if (isset($this->swId[$locale])) {
            return $this->swId[$locale];
        }

        return null;
    }

    /**
     * @param $swId
     * @param $locale
     */
    public function addSwId($swId, $locale)
    {
        $this->swId[$locale] = $swId;
    }

    /**
     * @param array $swId
     */
    public function setSwId($swId)
    {
        $this->swId = $swId;
    }

    /**
     * @return array
     */
    public function getSwIds()
    {
        return $this->swId;
    }
}
