<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Job\JobParameters\FormConfigurationProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

class ShopwareExport implements FormConfigurationProviderInterface
{
    protected $supportedJobNames;

    protected $localeRepository;

    public function __construct($supportedJobNames, LocaleRepositoryInterface $localeRepository)
    {
        $this->supportedJobNames = $supportedJobNames;
        $this->localeRepository = $localeRepository;
    }

    /**
     * @return array
     */
    public function getFormConfiguration()
    {
        return [
            'rootCategory' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.rootCategory.label',
                    'help'  => 'basecom_shopware_connector.export.rootCategory.help'
                ]
            ],
            'locale'   => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->localeRepository->getActivatedLocaleCodes(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'basecom_shopware_connector.export.locale.label',
                    'help'     => 'basecom_shopware_connector.export.locale.help'
                ]
            ],
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

    /**
     * @return boolean
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}