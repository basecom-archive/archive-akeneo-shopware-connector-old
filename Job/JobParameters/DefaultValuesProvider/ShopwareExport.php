<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

class ShopwareExport implements DefaultValuesProviderInterface
{
    protected $supportedJobNames;

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
            'apiKey' => '',
            'userName' => '',
            'url' => '',
            'locale' => ''
        ];
    }

    /**
     * @param JobInterface $job
     * @return bool
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}