<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Basecom\Bundle\ShopwareConnectorBundle\Serializer\ShopwareProductSerializer;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * processes the product for the export to shopware
 *
 * Class ShopwareProductProcessor
 * @package Basecom\Bundle\ShopwareConnectorBundle\Processor
 */
class ShopwareProductProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var ShopwareProductSerializer */
    protected $serializer;

    /** @var ApiClient */
    protected $apiClient;

    /** @var LocaleRepositoryInterface */
    protected $localeManager;

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $url;

    /** @var string */
    protected $related;

    /** @var string */
    protected $similar;

    /** @var string */
    protected $filterAttributes;

    /** @var string */
    protected $locale;
    /** @var string */
    protected $currency;
    /** @var string */
    protected $articleNumber;
    /** @var string */
    protected $tax;
    /** @var string */
    protected $name;
    /** @var string */
    protected $description;
    /** @var string */
    protected $descriptionLong;
    /** @var string */
    protected $pseudoSales;
    /** @var string */
    protected $highlight;
    /** @var string */
    protected $keywords;
    /** @var string */
    protected $metaTitle;
    /** @var string */
    protected $priceGroupActive;
    /** @var string */
    protected $lastStock;
    /** @var string */
    protected $notification;
    /** @var string */
    protected $template;
    /** @var string */
    protected $supplier;
    /** @var string */
    protected $inStock;
    /** @var string */
    protected $stockMin;
    /** @var string */
    protected $weight;
    /** @var string */
    protected $len;
    /** @var string */
    protected $height;
    /** @var string */
    protected $ean;
    /** @var string */
    protected $minPurchase;
    /** @var string */
    protected $purchaseSteps;
    /** @var string */
    protected $maxPurchase;
    /** @var string */
    protected $purchaseUnit;
    /** @var string */
    protected $referenceUnit;
    /** @var string */
    protected $packUnit;
    /** @var string */
    protected $shippingFree;
    /** @var string */
    protected $releaseDate;
    /** @var string */
    protected $shippingTime;
    /** @var string */
    protected $width;
    /** @var string */
    protected $price;
    /** @var string */
    protected $pseudoPrice;
    /** @var string */
    protected $basePrice;
    /** @var string */
    protected $attr;

    /**
     * ShopwareProductProcessor constructor.
     *
     * @param ShopwareProductSerializer $serializer
     * @param LocaleRepositoryInterface $localeRepositoryInterface
     */
    public function __construct(ShopwareProductSerializer $serializer, LocaleRepositoryInterface $localeRepositoryInterface)
    {
        $this->serializer = $serializer;
        $this->localeManager = $localeRepositoryInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $this->apiClient = new ApiClient($this->url, $this->userName, $this->apiKey);
        $attributeMapping = $this->convertConfigurationVariablesToMappingArray();
        return $this->serializer->serialize($item, $attributeMapping, $this->localeManager->getActivatedLocaleCodes()[$this->locale], $this->filterAttributes, $this->apiClient, $this->currency);
    }

    /**
     * maps the configuration fields variables to an array
     *
     * @return array
     */
    protected function convertConfigurationVariablesToMappingArray()
    {
        $configArray = array(
            'articleNumber'     => $this->articleNumber,
            'name'              => $this->name,
            'description'       => $this->description,
            'descriptionLong'   => $this->descriptionLong,
            'pseudoSales'		=> $this->pseudoSales,
            'highlight'			=> $this->highlight,
            'keywords'          => $this->keywords,
            'metaTitle'			=> $this->metaTitle,
            'priceGroupActive'	=> $this->priceGroupActive,
            'lastStock'			=> $this->lastStock,
            'notification'		=> $this->notification,
            'template'			=> $this->template,
            'supplier'	        => $this->supplier,
            'inStock'			=> $this->inStock,
            'stockMin'			=> $this->stockMin,
            'weight'			=> $this->weight,
            'len'				=> $this->len,
            'height'			=> $this->height,
            'ean'				=> $this->ean,
            'minPurchase'		=> $this->minPurchase,
            'purchaseSteps'		=> $this->purchaseSteps,
            'maxPurchase'		=> $this->maxPurchase,
            'purchaseUnit'		=> $this->purchaseUnit,
            'referenceUnit'		=> $this->referenceUnit,
            'packUnit'			=> $this->packUnit,
            'shippingFree'		=> $this->shippingFree,
            'releaseDate'		=> $this->releaseDate,
            'shippingTime'		=> $this->shippingTime,
            'width'				=> $this->width,
            'price'				=> $this->price,
            'pseudoPrice'		=> $this->pseudoPrice,
            'basePrice'			=> $this->basePrice,
            'tax'               => $this->tax,
            'similar'           => $this->similar,
            'related'           => $this->related,
        );
        $attributes = explode(";",$this->attr);
        foreach($attributes as $attribute) {
            $attr = explode(":", $attribute);
            if(isset($attr[1])) $configArray[$attr[0]] = $attr[1];
        }
        return $configArray;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'locale' => [
                'type' => 'choice',
                'options' => [
                    'choices'   => $this->localeManager->getActivatedLocaleCodes(),
                    'required'  => true,
                    'select2'   => true,
                    'label'     => 'basecom_shopware_connector.export.locale.label',
                    'help'      => 'basecom_shopware_connector.export.locale.label'
                ]
            ],
            'currency' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.currency.label',
                    'help'  => 'basecom_shopware_connector.export.currency.label'
                ]
            ],
            'apiKey' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.apiKey.label',
                    'help'  => 'basecom_shopware_connector.export.apiKey.help'
                ]
            ],
            'userName' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.userName.label',
                    'help'  => 'basecom_shopware_connector.export.userName.help'
                ]
            ],
            'url' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.url.label',
                    'help'  => 'basecom_shopware_connector.export.url.help'
                ]
            ],
            'similar' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.similar.label',
                    'help'  => 'basecom_shopware_connector.export.similar.help'
                ]
            ],
            'related' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.related.label',
                    'help'  => 'basecom_shopware_connector.export.related.help'
                ]
            ],
            'filterAttributes' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.filterAttributes.label',
                    'help'  => 'basecom_shopware_connector.export.filterAttributes.help'
                ]
            ],
            'supplier' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.supplier.label',
                    'help'  => 'basecom_shopware_connector.export.supplier.help'
                ]
            ],
            'name' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.name.label',
                    'help'  => 'basecom_shopware_connector.export.name.help'
                ]
            ],
            'articleNumber' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.articleNumber.label',
                    'help'  => 'basecom_shopware_connector.export.articleNumber.help'
                ]
            ],
            'tax' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.tax.label',
                    'help'  => 'basecom_shopware_connector.export.tax.help'
                ]
            ],
            'template' => [
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
            'price' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.price.label',
                    'help'  => 'basecom_shopware_connector.export.price.help'
                ]
            ],
            'descriptionLong' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.descriptionLong.label',
                    'help'  => 'basecom_shopware_connector.export.descriptionLong.help'
                ]
            ],
            'metaTitle' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.metaTitle.label',
                    'help'  => 'basecom_shopware_connector.export.metaTitle.help'
                ]
            ],
            'description' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.description.label',
                    'help'  => 'basecom_shopware_connector.export.description.help'
                ]
            ],
            'keywords' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.keywords.label',
                    'help'  => 'basecom_shopware_connector.export.keywords.help'
                ]
            ],
            'purchaseUnit' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.purchaseUnit.label',
                    'help'  => 'basecom_shopware_connector.export.purchaseUnit.help'
                ]
            ],
            'referenceUnit' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.referenceUnit.label',
                    'help'  => 'basecom_shopware_connector.export.referenceUnit.help'
                ]
            ],
            'packUnit' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.packUnit.label',
                    'help'  => 'basecom_shopware_connector.export.packUnit.help'
                ]
            ],
            'notification' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.notification.label',
                    'help'  => 'basecom_shopware_connector.export.notification.help'
                ]
            ],
            'shippingTime' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.shippingTime.label',
                    'help'  => 'basecom_shopware_connector.export.shippingTime.help'
                ]
            ],
            'inStock' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.inStock.label',
                    'help'  => 'basecom_shopware_connector.export.inStock.help'
                ]
            ],
            'stockMin' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.stockMin.label',
                    'help'  => 'basecom_shopware_connector.export.stockMin.help'
                ]
            ],
            'releaseDate' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.releaseDate.label',
                    'help'  => 'basecom_shopware_connector.export.releaseDate.help'
                ]
            ],
            'pseudoSales' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.pseudoSales.label',
                    'help'  => 'basecom_shopware_connector.export.pseudoSales.help'
                ]
            ],
            'minPurchase' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.minPurchase.label',
                    'help'  => 'basecom_shopware_connector.export.minPurchase.help'
                ]
            ],
            'purchaseSteps' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.purchaseSteps.label',
                    'help'  => 'basecom_shopware_connector.export.purchaseSteps.help'
                ]
            ],
            'maxPurchase' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.maxPurchase.label',
                    'help'  => 'basecom_shopware_connector.export.maxPurchase.help'
                ]
            ],
            'weight' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.weight.label',
                    'help'  => 'basecom_shopware_connector.export.weight.help'
                ]
            ],
            'shippingFree' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.shippingFree.label',
                    'help'  => 'basecom_shopware_connector.export.shippingFree.help'
                ]
            ],
            'highlight' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.highlight.label',
                    'help'  => 'basecom_shopware_connector.export.highlight.help'
                ]
            ],
            'lastStock' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.lastStock.label',
                    'help'  => 'basecom_shopware_connector.export.lastStock.help'
                ]
            ],
            'ean' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.ean.label',
                    'help'  => 'basecom_shopware_connector.export.ean.help'
                ]
            ],
            'width' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.width.label',
                    'help'  => 'basecom_shopware_connector.export.width.help'
                ]
            ],
            'height' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.height.label',
                    'help'  => 'basecom_shopware_connector.export.height.help'
                ]
            ],
            'len' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.len.label',
                    'help'  => 'basecom_shopware_connector.export.len.help'
                ]
            ],
            'attr' => [
                'type'    => 'hidden',
                'options' => [
                    'label' => 'basecom_shopware_connector.export.attr.label',
                    'help'  => 'basecom_shopware_connector.export.attr.help',
                ]
            ]
        ];
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getFilterAttributes()
    {
        return $this->filterAttributes;
    }

    /**
     * @param mixed $filterAttributes
     */
    public function setFilterAttributes($filterAttributes)
    {
        $this->filterAttributes = $filterAttributes;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return mixed
     */
    public function getArticleNumber()
    {
        return $this->articleNumber;
    }

    /**
     * @param mixed $articleNumber
     */
    public function setArticleNumber($articleNumber)
    {
        $this->articleNumber = $articleNumber;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescriptionLong()
    {
        return $this->descriptionLong;
    }

    /**
     * @param mixed $descriptionLong
     */
    public function setDescriptionLong($descriptionLong)
    {
        $this->descriptionLong = $descriptionLong;
    }

    /**
     * @return mixed
     */
    public function getPseudoSales()
    {
        return $this->pseudoSales;
    }

    /**
     * @param mixed $pseudoSales
     */
    public function setPseudoSales($pseudoSales)
    {
        $this->pseudoSales = $pseudoSales;
    }

    /**
     * @return mixed
     */
    public function getHighlight()
    {
        return $this->highlight;
    }

    /**
     * @param mixed $highlight
     */
    public function setHighlight($highlight)
    {
        $this->highlight = $highlight;
    }

    /**
     * @return mixed
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param mixed $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * @return mixed
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * @param mixed $metaTitle
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * @return mixed
     */
    public function getPriceGroupActive()
    {
        return $this->priceGroupActive;
    }

    /**
     * @param mixed $priceGroupActive
     */
    public function setPriceGroupActive($priceGroupActive)
    {
        $this->priceGroupActive = $priceGroupActive;
    }

    /**
     * @return mixed
     */
    public function getLastStock()
    {
        return $this->lastStock;
    }

    /**
     * @param mixed $lastStock
     */
    public function setLastStock($lastStock)
    {
        $this->lastStock = $lastStock;
    }

    /**
     * @return mixed
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param mixed $notification
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return mixed
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @param mixed $supplier
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * @return mixed
     */
    public function getInStock()
    {
        return $this->inStock;
    }

    /**
     * @param mixed $inStock
     */
    public function setInStock($inStock)
    {
        $this->inStock = $inStock;
    }

    /**
     * @return mixed
     */
    public function getStockMin()
    {
        return $this->stockMin;
    }

    /**
     * @param mixed $stockMin
     */
    public function setStockMin($stockMin)
    {
        $this->stockMin = $stockMin;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param mixed $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return mixed
     */
    public function getLen()
    {
        return $this->len;
    }

    /**
     * @param mixed $len
     */
    public function setLen($len)
    {
        $this->len = $len;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return mixed
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @param mixed $ean
     */
    public function setEan($ean)
    {
        $this->ean = $ean;
    }

    /**
     * @return mixed
     */
    public function getMinPurchase()
    {
        return $this->minPurchase;
    }

    /**
     * @param mixed $minPurchase
     */
    public function setMinPurchase($minPurchase)
    {
        $this->minPurchase = $minPurchase;
    }

    /**
     * @return mixed
     */
    public function getPurchaseSteps()
    {
        return $this->purchaseSteps;
    }

    /**
     * @param mixed $purchaseSteps
     */
    public function setPurchaseSteps($purchaseSteps)
    {
        $this->purchaseSteps = $purchaseSteps;
    }

    /**
     * @return mixed
     */
    public function getMaxPurchase()
    {
        return $this->maxPurchase;
    }

    /**
     * @param mixed $maxPurchase
     */
    public function setMaxPurchase($maxPurchase)
    {
        $this->maxPurchase = $maxPurchase;
    }

    /**
     * @return mixed
     */
    public function getPurchaseUnit()
    {
        return $this->purchaseUnit;
    }

    /**
     * @param mixed $purchaseUnit
     */
    public function setPurchaseUnit($purchaseUnit)
    {
        $this->purchaseUnit = $purchaseUnit;
    }

    /**
     * @return mixed
     */
    public function getReferenceUnit()
    {
        return $this->referenceUnit;
    }

    /**
     * @param mixed $referenceUnit
     */
    public function setReferenceUnit($referenceUnit)
    {
        $this->referenceUnit = $referenceUnit;
    }

    /**
     * @return mixed
     */
    public function getPackUnit()
    {
        return $this->packUnit;
    }

    /**
     * @param mixed $packUnit
     */
    public function setPackUnit($packUnit)
    {
        $this->packUnit = $packUnit;
    }

    /**
     * @return mixed
     */
    public function getShippingFree()
    {
        return $this->shippingFree;
    }

    /**
     * @param mixed $shippingFree
     */
    public function setShippingFree($shippingFree)
    {
        $this->shippingFree = $shippingFree;
    }

    /**
     * @return mixed
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * @param mixed $releaseDate
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;
    }

    /**
     * @return mixed
     */
    public function getShippingTime()
    {
        return $this->shippingTime;
    }

    /**
     * @param mixed $shippingTime
     */
    public function setShippingTime($shippingTime)
    {
        $this->shippingTime = $shippingTime;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getPseudoPrice()
    {
        return $this->pseudoPrice;
    }

    /**
     * @param mixed $pseudoPrice
     */
    public function setPseudoPrice($pseudoPrice)
    {
        $this->pseudoPrice = $pseudoPrice;
    }

    /**
     * @return mixed
     */
    public function getBasePrice()
    {
        return $this->basePrice;
    }

    /**
     * @param mixed $basePrice
     */
    public function setBasePrice($basePrice)
    {
        $this->basePrice = $basePrice;
    }

    /**
     * @return mixed
     */
    public function getAttr()
    {
        return $this->attr;
    }

    /**
     * @param mixed $attr
     */
    public function setAttr($attr)
    {
        $this->attr = $attr;
    }

    /**
     * @return mixed
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param mixed $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * @return mixed
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * @param mixed $related
     */
    public function setRelated($related)
    {
        $this->related = $related;
    }

    /**
     * @return mixed
     */
    public function getSimilar()
    {
        return $this->similar;
    }

    /**
     * @param mixed $similar
     */
    public function setSimilar($similar)
    {
        $this->similar = $similar;
    }
}
