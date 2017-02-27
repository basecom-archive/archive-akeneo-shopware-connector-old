<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\ViewElement\Checker;

use Pim\Bundle\EnrichBundle\ViewElement\Checker\VisibilityCheckerInterface;

class ShopwareExportVisibilityChecker implements VisibilityCheckerInterface
{
    protected $jobNames = ['shopware_product_export'];

    public function isVisible(array $config = [], array $context = [])
    {
        if (!isset($context['jobInstance'])) {
            throw new \InvalidArgumentException('A "jobInstance" should be provided in the context.');
        }

        $jobInstance = $context['jobInstance'];

        return in_array($jobInstance->getJobName(), $this->jobNames);
    }
}