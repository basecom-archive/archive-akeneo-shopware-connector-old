<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Entity;

use Akeneo\Component\FileStorage\Model\FileInfo as PimFileInfo;

/**
 * Overrides original FileInfo class to add
 * the Shopware mediaId.
 *
 * Class FileInfo
 * @package Basecom\Bundle\ShopwareConnectorBundle\Entity
 */
class FileInfo extends PimFileInfo
{
    /** @var integer */
    protected $swMediaId;

    /**
     * @return integer
     */
    public function getSwMediaId()
    {
        return $this->swMediaId;
    }

    /**
     * @param integer $swMediaId
     */
    public function setSwMediaId($swMediaId)
    {
        $this->swMediaId = $swMediaId;
    }
}
