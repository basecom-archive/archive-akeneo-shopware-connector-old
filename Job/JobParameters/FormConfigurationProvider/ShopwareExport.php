<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Job\JobParameters\FormConfigurationProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

class ShopwareExport implements FormConfigurationProviderInterface
{
    protected $supportedJobNames;

    protected $localeRepository;
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    public function __construct($supportedJobNames, LocaleRepositoryInterface $localeRepository, CategoryRepositoryInterface $categoryRepository)
    {
        $this->supportedJobNames = $supportedJobNames;
        $this->localeRepository = $localeRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @return array
     */
    public function getFormConfiguration()
    {
        return [
            'rootCategory' => [
                'type' => 'choice',
                'options' => [
                    'choices' => $this->getCategoryChoices(),
                    'select2'  => true,
                    'label' => 'basecom_shopware_connector.export.rootCategory.label',
                    'help'  => 'basecom_shopware_connector.export.rootCategory.help',
                    'required' => true
                ]
            ],
            'locale'   => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->parseActivatedLocaleCodes(),
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
                    'help'  => 'basecom_shopware_connector.export.url.help',
                    'required' => true
                ]
            ],
            'userName' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.userName.label',
                    'help'  => 'basecom_shopware_connector.export.userName.help',
                    'required' => true
                ]
            ],
            'apiKey'   => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.apiKey.label',
                    'help'  => 'basecom_shopware_connector.export.apiKey.help',
                    'required' => true
                ]
            ]
        ];
    }

    protected function parseActivatedLocaleCodes()
    {
        $localeArray = $this->localeRepository->getActivatedLocaleCodes();

        return array_combine($localeArray, $localeArray);
    }


    /**
     * @return array
     */
    protected function getCategoryChoices() {
        $categoryChoices = [];
        $trees = $this->categoryRepository->getTrees();

        /** @var Category $tree */
        foreach($trees as $tree) {
            $tree->setLocale('en_US');
            $categoryChoices[$tree->getCode()] = $tree->getLabel();
        }

        return $categoryChoices;
    }

    /**
     * @return boolean
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}