<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class BasecomShopwareConnectorExtension.
 * @package Basecom\Bundle\ShopwareConnectorBundle\DependencyInjection
 */
class BasecomShopwareConnectorExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('jobs.yml');
        $loader->load('job_parameters.yml');
        $loader->load('steps.yml');
        $loader->load('cleaners.yml');
        $loader->load('readers.yml');
        $loader->load('processors.yml');
        $loader->load('writers.yml');
        $loader->load('entities.yml');
        $loader->load('serializers.yml');
        $loader->load('controllers.yml');
        $loader->load('services.yml');
        $loader->load('view_elements/job_profile.yml');
        $loader->load('view_elements.yml');
    }
}
