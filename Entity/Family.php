<?php
/**
 * Created by PhpStorm.
 * User: ado
 * Date: 04.04.16
 * Time: 16:55
 */

namespace Basecom\Bundle\ShopwareConnectorBundle\Entity;

use Pim\Bundle\CatalogBundle\Entity\Family as PimFamily;

class Family extends PimFamily
{
    protected $sid;

    /**
     * @return mixed
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * @param mixed $sid
     */
    public function setSid($sid)
    {
        $this->sid = $sid;
    }
}