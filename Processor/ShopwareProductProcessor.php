<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Basecom\Bundle\ShopwareConnectorBundle\Serializer\ShopwareProductSerializer;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

class ShopwareProductProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var ShopwareProductSerializer */
    protected $serializer;

    protected $rootDir;

    protected $apiClient;

    /** @var LocaleRepositoryInterface */
    protected $localeManager;

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $url;

    protected $related;

    protected $similar;

    protected $filterAttributes;

    protected $locale;
    protected $currency;
    protected $articleNumber;
    protected $tax;
    protected $name;
    protected $description;
    protected $descriptionLong;
    protected $pseudoSales;
    protected $highlight;
    protected $keywords;
    protected $metaTitle;
    protected $priceGroupActive;
    protected $lastStock;
    protected $notification;
    protected $template;
    protected $supplier;
    protected $inStock;
    protected $stockMin;
    protected $weight;
    protected $len;
    protected $height;
    protected $ean;
    protected $minPurchase;
    protected $purchaseSteps;
    protected $maxPurchase;
    protected $purchaseUnit;
    protected $referenceUnit;
    protected $packUnit;
    protected $shippingFree;
    protected $releaseDate;
    protected $shippingTime;
    protected $width;
    protected $price;
    protected $pseudoPrice;
    protected $basePrice;
    protected $attr;

    /**
     * ShopwareProductProcessor constructor.
     * @param ShopwareProductSerializer $serializer
     */
    public function __construct(ShopwareProductSerializer $serializer, LocaleRepositoryInterface $localeRepositoryInterface)
    {
        $this->serializer = $serializer;
        $this->localeManager = $localeRepositoryInterface;
    }

    public function process($item)
    {
        $this->apiClient = new ApiClient($this->url, $this->userName, $this->apiKey);
        $attributeMapping = $this->convertConfigurationVariablesToMappingArray();
        return $this->serializer->serialize($item, $attributeMapping, $this->locale, $this->filterAttributes, $this->apiClient, $this->currency);
    }

    protected function convertConfigurationVariablesToMappingArray() {
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

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function getConfigurationFields()
    {
        return [
            'locale' => [
                'type' => 'choice',
                'options' => [
                    'choices'   => $this->localeManager->getActivatedLocaleCodes(),
                    'required'  => true,
                    'select2'   => true,
                    'label'     => 'Locale',
                    'help'      => 'locale'
                ]
            ],
            'currency' => [
                'options' => [
                    'label' => 'Währung',
                    'help'  => ' Geben Sie hier die zu exportierende Währung ein'
                ]
            ],
            'apiKey' => [
                'options' => [
                    'label' => 'API-Key',
                    'help'  => 'pim_connector.import.filePath.help'
                ]
            ],
            'userName' => [
                'options' => [
                    'label' => 'Username',
                    'help'  => 'Shopware Api-Username'
                ]
            ],
            'url' => [
                'options' => [
                    'label' => 'URL',
                    'help'  => 'Shopware Api-URL'
                ]
            ],
            'similar' => [
                'options' => [
                    'label' => 'Ähnliche Artikel',
                    'help'  => 'Geben Sie hier den code des Akeneo-Association Type für ähnliche Artikel ein'
                ]
            ],
            'related' => [
                'options' => [
                    'label' => 'Zubehör Artikel',
                    'help'  => 'Geben Sie hier den code des Akeneo-Association Type für Zubehör Artikel ein'
                ]
            ],
            'filterAttributes' => [
                'options' => [
                    'label' => 'Filter Attribute',
                    'help'  => 'the code of the attributes that should be property values seperated by comma'
                ]
            ],
            'supplier' => [
                'options' => [
                    'label' => 'Hersteller',
                    'help'  => 'Geben Sie hier den code des Akeneo-Hersteller-Attributs ein'
                ]
            ],
            'name' => [
                'options' => [
                    'label' => 'Artikel-Bezeichnung',
                    'help'  => 'Geben Sie hier den code des Akeneo-Artikel-Bezeichnung-Attributs ein'
                ]
            ],
            'articleNumber' => [
                'options' => [
                    'label' => 'Artikelnummer',
                    'help'  => 'Geben Sie hier den code des Akeneo-Artikelnummer-Attributs ein'
                ]
            ],
            'tax' => [
                'options' => [
                    'label' => 'MwSt',
                    'help'  => 'Geben Sie hier den code des Akeneo-MwSt-Attributs ein'
                ]
            ],
            'template' => [
                'options' => [
                    'label' => 'Template',
                    'help'  => 'Geben Sie hier den code des Akeneo-Template-Attributs ein'
                ]
            ],
            'priceGroupActive' => [
                'options' => [
                    'label' => 'Preisgruppe aktiv',
                    'help'  => 'Geben Sie hier den code des Akeneo-Preisgruppe-aktiv-Attributs ein'
                ]
            ],
            'price' => [
                'options' => [
                    'label' => 'Preis',
                    'help'  => 'Geben Sie hier den code des Akeneo-Preis-Attributs ein'
                ]
            ],
            'descriptionLong' => [
                'options' => [
                    'label' => 'Beschreibung',
                    'help'  => 'Geben Sie hier den code des Akeneo-Beschreibung-Attributs ein'
                ]
            ],
            'metaTitle' => [
                'options' => [
                    'label' => 'Meta Titel',
                    'help'  => 'Geben Sie hier den code des Akeneo-Metatitel-Attributs ein'
                ]
            ],
            'description' => [
                'options' => [
                    'label' => 'Kurzbeschreibung',
                    'help'  => 'Geben Sie hier den code des Akeneo-Kurzeschreibung-Attributs ein'
                ]
            ],
            'keywords' => [
                'options' => [
                    'label' => 'Keywords',
                    'help'  => 'Geben Sie hier den code des Akeneo-Keywords-Attributs ein'
                ]
            ],
            'purchaseUnit' => [
                'options' => [
                    'label' => 'Maßeinheit',
                    'help'  => 'Enter the code of the pims purchaseUnit attribute'
                ]
            ],
            'referenceUnit' => [
                'options' => [
                    'label' => 'Grundeinheit',
                    'help'  => 'Enter the code of the pims referenceUnit attribute'
                ]
            ],
            'packUnit' => [
                'options' => [
                    'label' => 'Verpackungseinheit',
                    'help'  => 'Enter the code of the pims packUnit attribute'
                ]
            ],
            'notification' => [
                'options' => [
                    'label' => 'E-Mail-Benachrichtigung',
                    'help'  => 'Geben Sie hier den code des Akeneo-E-Mail-Benachrichtigung-Attributs ein'
                ]
            ],
            'shippingTime' => [
                'options' => [
                    'label' => 'Lieferzeit (in Tagen)',
                    'help'  => 'Enter the code of the pims shippingTime attribute'
                ]
            ],
            'inStock' => [
                'options' => [
                    'label' => 'Lagerbestand',
                    'help'  => 'Enter the code of the pims inStock attribute'
                ]
            ],
            'stockMin' => [
                'options' => [
                    'label' => 'Lager-Mindestbestand',
                    'help'  => 'Enter the code of the pims stockMin attribute'
                ]
            ],
            'releaseDate' => [
                'options' => [
                    'label' => 'Erscheinungsdatum',
                    'help'  => 'Enter the code of the pims releaseDate attribute'
                ]
            ],
            'pseudoSales' => [
                'options' => [
                    'label' => 'Pseudo Verkäufe',
                    'help'  => 'Enter the code of the pims pseudoSales attribute'
                ]
            ],
            'minPurchase' => [
                'options' => [
                    'label' => 'Mindestabnahme',
                    'help'  => 'Enter the code of the pims minPurchase attribute'
                ]
            ],
            'purchaseSteps' => [
                'options' => [
                    'label' => 'Staffelung',
                    'help'  => 'Geben Sie hier den code des Akeneo Staffelung-Attributs ein'
                ]
            ],
            'maxPurchase' => [
                'options' => [
                    'label' => 'Maximalabnahme',
                    'help'  => 'Enter the code of the pims maxPurchase attribute'
                ]
            ],
            'weight' => [
                'options' => [
                    'label' => 'Gewicht (in KG)',
                    'help'  => 'Enter the code of the pims weight attribute'
                ]
            ],
            'shippingFree' => [
                'options' => [
                    'label' => 'Versandkostenfrei',
                    'help'  => 'Enter the code of the pims shippingFree attribute'
                ]
            ],
            'highlight' => [
                'options' => [
                    'label' => 'Artikel hervorheben',
                    'help'  => 'Enter the code of the pims highlight attribute'
                ]
            ],
            'lastStock' => [
                'options' => [
                    'label' => 'Abverkauf',
                    'help'  => 'Enter the code of the pims lastStock attribute'
                ]
            ],
            'ean' => [
                'options' => [
                    'label' => 'EAN',
                    'help'  => 'Enter the code of the pims ean attribute'
                ]
            ],
            'width' => [
                'options' => [
                    'label' => 'Breite',
                    'help'  => 'Enter the code of the pims width attribute'
                ]
            ],
            'height' => [
                'options' => [
                    'label' => 'Höhe',
                    'help'  => 'Enter the code of the pims height attribute'
                ]
            ],
            'len' => [
                'options' => [
                    'label' => 'Länge',
                    'help'  => 'Enter the code of the pims len attribute'
                ]
            ],
            'attr' => [
                'type'    => 'hidden',
                'options' => [
                    'label' => 'Zusätzliche Attribute',
                    'help'  => 'Zusätzliche Shopware Attribute',
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