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
    protected $swIds;

    /**
     * @return array
     */
    public function getSwId($locale)
    {
        if (isset($this->swIds[$locale])) {
            return $this->swIds[$locale];
        }

        return null;
    }

    /**
     * @param $swId
     * @param $locale
     */
    public function addSwId($swId, $locale)
    {
        $this->swIds[$locale] = $swId;
    }

    /**
     * @param array $swIds
     */
    public function setSwIds($swIds)
    {
        $this->swIds = $swIds;
    }

    /**
     * @return array
     */
    public function getSwIds()
    {
        return $this->swIds;
    }
}
