<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Job\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\AttributeRepository;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

/**
 * Class ProductExport
 * @package Basecom\Bundle\ShopwareConnectorBundle\Job\JobParameters
 */
class ProductExport implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface, FormConfigurationProviderInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var ChannelRepositoryInterface
     */
    protected $channelRepository;

    /**
     * @var LocaleRepositoryInterface
     */
    protected $localeRepository;
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var CurrencyRepositoryInterface
     */
    private $currencyRepository;
    /**
     * @var UserContext
     */
    private $userContext;
    /**
     * @var array
     */
    private $attributeChoices;

    /**
     * ProductExport constructor.
     * @param UserContext $userContext
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ChannelRepositoryInterface $channelRepository
     * @param LocaleRepositoryInterface $localeRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param CurrencyRepositoryInterface $currencyRepository
     */
    public function __construct(
        UserContext $userContext,
        CategoryRepositoryInterface $categoryRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        AttributeRepositoryInterface $attributeRepository,
        CurrencyRepositoryInterface $currencyRepository
    )
    {
        $this->categoryRepository = $categoryRepository;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->currencyRepository = $currencyRepository;
        $this->userContext = $userContext;
    }

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
                ],
                'rootCategory' => [
                    new NotBlank(['groups' => 'Execution'])
                ],
                'channel' => [
                    new NotBlank(['groups' => 'Execution'])
                ],
                'locale' => [
                    new NotBlank(['groups' => 'Execution'])
                ],
                'currency' => [],
                'filterAttributes' => [],
                'supplier' => [
                    new NotBlank(['groups' => 'Execution'])
                ],
                'name' => [
                    new NotBlank(['groups' => 'Execution'])
                ],
                'articleNumber' => [
                    new NotBlank(['groups' => 'Execution'])
                ],
//                'tax' => [
//                    new NotBlank(['groups' => 'Execution'])
//                ],
                'template' => [],
                'priceGroupActive' => [],
                'price' => [
                    new NotBlank(['groups' => 'Execution'])
                ],
                'descriptionLong'   => [],
                'metaTitle'         => [],
                'description'       => [],
                'keywords'          => [],
                'purchaseUnit'      => [],
                'referenceUnit'     => [],
                'packUnit'          => [],
                'notification'      => [],
                'shippingTime'      => [],
                'inStock'           => [],
                'stockMin'          => [],
                'releaseDate'       => [],
                'pseudoSales'       => [],
                'pseudoPrice'       => [],
                'basePrice'         => [],
                'minPurchase'       => [],
                'purchaseSteps'     => [],
                'maxPurchase'       => [],
                'weight'            => [],
                'shippingFree'      => [],
                'highlight'         => [],
                'lastStock'         => [],
                'ean'               => [],
                'width'             => [],
                'height'            => [],
                'len'               => [],
                'attr'              => []
            ]
        ]);
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        return [
            'apiKey'            => '',
            'userName'          => '',
            'url'               => '',
            'rootCategory'      => '',
            'channel'           => '',
            'locale'            => '',
            'currency'          => '',
            'filterAttributes'  => '',
            'supplier'          => '',
            'name'              => '',
            'articleNumber'     => '',
//            'tax'               => '',
            'template'          => '',
            'priceGroupActive'  => '',
            'price'             => '',
            'descriptionLong'   => '',
            'metaTitle'         => '',
            'description'       => '',
            'keywords'          => '',
            'purchaseUnit'      => '',
            'referenceUnit'     => '',
            'packUnit'          => '',
            'notification'      => '',
            'shippingTime'      => '',
            'inStock'           => '',
            'stockMin'          => '',
            'releaseDate'       => '',
            'pseudoSales'       => '',
            'pseudoPrice'       => '',
            'basePrice'         => '',
            'minPurchase'       => '',
            'purchaseSteps'     => '',
            'maxPurchase'       => '',
            'weight'            => '',
            'shippingFree'      => '',
            'highlight'         => '',
            'lastStock'         => '',
            'ean'               => '',
            'width'             => '',
            'height'            => '',
            'len'               => '',
            'attr'              => ''
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
            ],
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
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => array_combine($this->channelRepository->getChannelCodes(), $this->channelRepository->getChannelCodes()),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'basecom_shopware_connector.export.channel.label',
                    'help'     => 'basecom_shopware_connector.export.channel.label'
                ]
            ],
            'locale' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->parseActivatedLocaleCodes(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'basecom_shopware_connector.export.locale.label',
                    'help'     => 'basecom_shopware_connector.export.locale.help'
                ]
            ],
            'currency' => [
                'type' => 'choice',
                'options' => [
                    'required' => true,
                    'select2'  => true,
                    'choices' => $this->parseActivatedCurrencyCodes(),
                    'label' => 'basecom_shopware_connector.export.currency.label',
                    'help'  => 'basecom_shopware_connector.export.currency.label'
                ]
            ],
            'filterAttributes' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.filterAttributes.label',
                    'help'  => 'basecom_shopware_connector.export.filterAttributes.help'
                ]
            ],
            'supplier'         => [
                'type' => 'choice',
                'options' => [
                    'select2'  => true,
                    'choices' => $this->getAttributeChoices(),
                    'label' => 'basecom_shopware_connector.export.supplier.label',
                    'help'  => 'basecom_shopware_connector.export.supplier.help',
                    'required' => true
                ]
            ],
            'name'             => [
                'type' => 'choice',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.name.label',
                    'help'  => 'basecom_shopware_connector.export.name.help',
                    'required' => true,
                    'select2'  => true,
                    'choices' => $this->getAttributeChoices()
                ]
            ],
            'articleNumber'    => [
                'type' => 'choice',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.articleNumber.label',
                    'help'  => 'basecom_shopware_connector.export.articleNumber.help',
                    'required' => true,
                    'select2'  => true,
                    'choices' => $this->getAttributeChoices()
                ]
            ],
            'template'         => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.template.label',
                    'help'  => 'basecom_shopware_connector.export.template.help'
                ]
            ],
            'priceGroupActive' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.priceGroupActive.label',
                    'help'  => 'basecom_shopware_connector.export.priceGroupActive.help'
                ]
            ],
            'price'            => [
                'type' => 'choice',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.price.label',
                    'help'  => 'basecom_shopware_connector.export.price.help',
                    'required' => true,
                    'select2'  => true,
                    'choices' => $this->getAttributeChoices()
                ]
            ],
            'descriptionLong'  => [
                'type' => 'choice',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.descriptionLong.label',
                    'help'  => 'basecom_shopware_connector.export.descriptionLong.help',
                    'select2'  => true,
                    'choices' => $this->getAttributeChoices()
                ]
            ],
            'metaTitle'        => [
                'type' => 'choice',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.metaTitle.label',
                    'help'  => 'basecom_shopware_connector.export.metaTitle.help',
                    'select2'  => true,
                    'choices' => $this->getAttributeChoices()
                ]
            ],
            'description'      => [
                'type' => 'choice',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.description.label',
                    'help'  => 'basecom_shopware_connector.export.description.help',
                    'select2'  => true,
                    'choices' => $this->getAttributeChoices()
                ]
            ],
            'keywords'         => [
                'type' => 'choice',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.keywords.label',
                    'help'  => 'basecom_shopware_connector.export.keywords.help',
                    'select2'  => true,
                    'choices' => $this->getAttributeChoices()
                ]
            ],
            'purchaseUnit'     => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.purchaseUnit.label',
                    'help'  => 'basecom_shopware_connector.export.purchaseUnit.help'
                ]
            ],
            'referenceUnit'    => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.referenceUnit.label',
                    'help'  => 'basecom_shopware_connector.export.referenceUnit.help'
                ]
            ],
            'packUnit'         => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.packUnit.label',
                    'help'  => 'basecom_shopware_connector.export.packUnit.help'
                ]
            ],
            'notification'     => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.notification.label',
                    'help'  => 'basecom_shopware_connector.export.notification.help'
                ]
            ],
            'shippingTime'     => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.shippingTime.label',
                    'help'  => 'basecom_shopware_connector.export.shippingTime.help'
                ]
            ],
            'inStock'          => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.inStock.label',
                    'help'  => 'basecom_shopware_connector.export.inStock.help'
                ]
            ],
            'stockMin'         => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.stockMin.label',
                    'help'  => 'basecom_shopware_connector.export.stockMin.help'
                ]
            ],
            'releaseDate'      => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.releaseDate.label',
                    'help'  => 'basecom_shopware_connector.export.releaseDate.help'
                ]
            ],
            'pseudoSales'      => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.pseudoSales.label',
                    'help'  => 'basecom_shopware_connector.export.pseudoSales.help'
                ]
            ],
            'pseudoPrice'      => [
                'type' => 'choice',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.pseudoPrice.label',
                    'help'  => 'basecom_shopware_connector.export.pseudoPrice.help',
                    'select2'  => true,
                    'choices' => $this->getAttributeChoices()
                ]
            ],
            'basePrice'      => [
                'type' => 'choice',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.basePrice.label',
                    'help'  => 'basecom_shopware_connector.export.basePrice.help',
                    'select2'  => true,
                    'choices' => $this->getAttributeChoices()
                ]
            ],
            'minPurchase'      => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.minPurchase.label',
                    'help'  => 'basecom_shopware_connector.export.minPurchase.help'
                ]
            ],
            'purchaseSteps'    => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.purchaseSteps.label',
                    'help'  => 'basecom_shopware_connector.export.purchaseSteps.help'
                ]
            ],
            'maxPurchase'      => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.maxPurchase.label',
                    'help'  => 'basecom_shopware_connector.export.maxPurchase.help'
                ]
            ],
            'weight'           => [
                'type' => 'choice',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.weight.label',
                    'help'  => 'basecom_shopware_connector.export.weight.help',
                    'select2'  => true,
                    'choices' => $this->getAttributeChoices()
                ]
            ],
            'shippingFree'     => [
                'type' => 'choice',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.shippingFree.label',
                    'help'  => 'basecom_shopware_connector.export.shippingFree.help',
                    'select2'  => true,
                    'choices' => $this->getAttributeChoices()
                ]
            ],
            'highlight'        => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.highlight.label',
                    'help'  => 'basecom_shopware_connector.export.highlight.help'
                ]
            ],
            'lastStock'        => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.lastStock.label',
                    'help'  => 'basecom_shopware_connector.export.lastStock.help'
                ]
            ],
            'ean'              => [
                'type' => 'choice',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.ean.label',
                    'help'  => 'basecom_shopware_connector.export.ean.help',
                    'select2'  => true,
                    'choices' => $this->getAttributeChoices()
                ]
            ],
            'width'            => [
                'type' => 'choice',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.width.label',
                    'help'  => 'basecom_shopware_connector.export.width.help',
                    'select2'  => true,
                    'choices' => $this->getAttributeChoices()
                ]
            ],
            'height'           => [
                'type' => 'choice',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.height.label',
                    'help'  => 'basecom_shopware_connector.export.height.help',
                    'select2'  => true,
                    'choices' => $this->getAttributeChoices()
                ]
            ],
            'len'              => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.len.label',
                    'help'  => 'basecom_shopware_connector.export.len.help'
                ]
            ],
            'attr'             => [
                'type'    => 'hidden',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.attr.label',
                    'help'  => 'basecom_shopware_connector.export.attr.help',
                ]
            ]
        ];
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

    protected function parseActivatedLocaleCodes()
    {
        $localeArray = $this->localeRepository->getActivatedLocaleCodes();

        return array_combine($localeArray, $localeArray);
    }

    protected function parseActivatedCurrencyCodes()
    {
        $currencyArray = $this->currencyRepository->getActivatedCurrencyCodes();

        return array_combine($currencyArray, $currencyArray);
    }

    protected function getAttributeChoices()
    {
        if(0 === count($this->attributeChoices)) {
            $translatedLabels = $this->attributeRepository->getAttributesAsArray(true, $this->userContext->getCurrentLocaleCode());
            $this->attributeChoices = array_column($translatedLabels, 'label', 'code');
        }

        return $this->attributeChoices;
    }

    /**
     * @param JobInterface $job
     * @return bool
     */
    public function supports(JobInterface $job)
    {
        return $job->getName() == 'shopware_product_export';
    }
}
