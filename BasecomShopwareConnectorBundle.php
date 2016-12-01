<?php

namespace Basecom\Bundle\ShopwareConnectorBundle;

use Basecom\Bundle\ShopwareConnectorBundle\DependencyInjection\EnterprisePass\EnterprisePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BasecomShopwareConnectorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EnterprisePass());
    }
}
