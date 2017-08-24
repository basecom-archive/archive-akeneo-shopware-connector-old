<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

/**
 * @author  Amir El Sayed <elsayed@basecom.de>
 *
 * Class ShopwareExport
 * @package Basecom\Bundle\ShopwareConnectorBundle\Job\JobParameters\DefaultValuesProvider
 */
class ShopwareExport implements DefaultValuesProviderInterface
{
    protected $supportedJobNames;

    /**
     * ShopwareExport constructor.
     *
     * @param $supportedJobNames
     */
    public function __construct($supportedJobNames)
    {
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        return [
            'rootCategory' => '',
            'apiKey'       => '',
            'userName'     => '',
            'url'          => '',
            'shop'         => '1',
            'locale'       => '',
        ];
    }

    /**
     * @param JobInterface $job
     *
     * @return bool
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
