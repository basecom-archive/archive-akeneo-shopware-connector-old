<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Locale;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class ShopwareExport implements ConstraintCollectionProviderInterface
{
    protected $supportedJobNames;

    public function __construct($supportedJobNames)
    {
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * @return Collection
     */
    public function getConstraintCollection()
    {
        return new Collection([
            'fields' => [
                'rootCategory' => [
                    new NotBlank(['groups' => 'Execution'])
                ],
                'apiKey' => [
                    new NotBlank(['groups' => 'Execution'])
                ],
                'username' => [
                    new NotBlank(['groups' => 'Execution'])
                ],
                'url' => [
                    new NotBlank(['groups' => 'Execution']),
                    new Url(['groups' => 'Execution'])
                ],
                'locale' => [
                    new Locale(['groups' => 'Execution'])
                ],
            ]
        ]);
    }

    /**
     * @return boolean
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}