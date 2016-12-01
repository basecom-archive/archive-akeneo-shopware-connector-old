<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\DependencyInjection\EnterprisePass;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EnterprisePass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $isEnterprise = $container->has('pim_installer.directories_registry');

        if($isEnterprise) {
            $mediaWriter = $container->getDefinition('basecom_shopware_connector.api.shopware_media_writer');
            $mediaWriter->setClass($container->getParameter('basecom_shopware_connector.api.shopware_media_writer_enterprise.class'));
        }
    }
}