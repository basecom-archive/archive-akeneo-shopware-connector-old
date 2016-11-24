<?php

namespace Basecom\Bundle\ShopwareConnectorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BasecomShopwareConnectorBundle extends Bundle
{
    public function getParent()
    {
        return 'AkeneoFileStorageBundle';
    }
}
