<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Entity;

use Akeneo\Component\FileStorage\Model\FileInfo as PimFileInfo;

/**
 * Overrides original FileInfo class to add
 * the Shopware mediaId.
 *
 * Class FileInfo
 */
class FileInfo extends PimFileInfo
{
    /** @var int */
    protected $swMediaId;

    /**
     * @return int
     */
    public function getSwMediaId()
    {
        return $this->swMediaId;
    }

    /**
     * @param int $swMediaId
     */
    public function setSwMediaId($swMediaId)
    {
        $this->swMediaId = $swMediaId;
    }
}
