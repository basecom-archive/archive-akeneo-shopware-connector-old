<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Entity;

use Akeneo\Component\FileStorage\Model\FileInfo as PimFileInfo;

class FileInfo extends PimFileInfo
{
    /** @var integer */
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