<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Job\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class AttributeSetup implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface, FormConfigurationProviderInterface
{
    /**
     * @return Collection
     */
    public function getConstraintCollection()
    {
        return new Collection([
            'fields' => [
                'apiKey' => [
                    new NotBlank(['groups' => 'Execution'])
                ],
                'userName' => [
                    new NotBlank(['groups' => 'Execution'])
                ],
                'url' => [
                    new NotBlank(['groups' => 'Execution']),
                    new Url(['groups' => 'Execution'])
                ]
            ]
        ]);
    }

    /**
     * @return boolean
     */
    public function supports(JobInterface $job)
    {
        return $job->getName() == 'shopware_attribute_setup';
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        return [
            'apiKey' => '',
            'userName' => '',
            'url' => ''
        ];
    }

    /**
     * @return array
     */
    public function getFormConfiguration()
    {
        return [
            'url'      => [
                'type' => 'url',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.url.label',
                    'help'  => 'basecom_shopware_connector.export.url.help'
                ]
            ],
            'userName' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.userName.label',
                    'help'  => 'basecom_shopware_connector.export.userName.help'
                ]
            ],
            'apiKey'   => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.apiKey.label',
                    'help'  => 'basecom_shopware_connector.export.apiKey.help'
                ]
            ]
        ];
    }
}