<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Reader;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;

class ShopwareAttributeReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $url;

    protected $jsonData;

    protected $groupCount;

    protected $attributeCount;

    protected $groupTotal;

    protected $attributeTotal;

    protected $attributesAlreadyDone;

    /**
     * ShopwareAttributeReader constructor.
     * @param $attributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }


    public function read()
    {
        if($this->jsonData === null) {
            echo "Attribute import...\n";
            $client = new ApiClient($this->url, $this->userName, $this->apiKey);
            $this->jsonData = $client->get('propertyGroups');
            $this->groupCount = 0;
            $this->attributeCount = 0;
            $this->groupTotal = $this->jsonData['total'];
            $this->attributeTotal = count($this->jsonData['data'][$this->groupCount]['options']);
            $this->attributesAlreadyDone = [];
        }
        if($this->attributeCount >= $this->attributeTotal) {
            $this->groupCount++;
            if($this->groupCount >= $this->groupTotal) {
                if($this->groupCount >= $this->groupTotal+12)
                {
                    return null;
                } elseif($this->groupCount >= $this->groupTotal+11) {
                    return $this->importShortDescription();
                } elseif($this->groupCount >= $this->groupTotal+10) {
                    return $this->importPseudoSales();
                } elseif($this->groupCount >= $this->groupTotal+9) {
                    return $this->importHighlight();
                } elseif($this->groupCount >= $this->groupTotal+8) {
                    return $this->importKeywords();
                } elseif($this->groupCount >= $this->groupTotal+7) {
                    return $this->importMetaTitle();
                } elseif($this->groupCount >= $this->groupTotal+6) {
                    return $this->importPriceGroupActive();
                } elseif($this->groupCount >= $this->groupTotal+5) {
                    return $this->importNotification();
                } elseif($this->groupCount >= $this->groupTotal+4) {
                    return $this->importInStock();
                } elseif($this->groupCount >= $this->groupTotal+3) {
                    return $this->importImage();
                } elseif($this->groupCount >= $this->groupTotal+2) {
                    return $this->importNameAttribute();
                } elseif($this->groupCount >= $this->groupTotal+1) {
                    return $this->importDescriptionAttribute();
                }
                return $this->importPriceAttribute();
            }
            $this->attributeCount = 0;
            $this->attributeTotal = count($this->jsonData['data'][$this->groupCount]['options']);
        }

        $alreadyDone = in_array($this->jsonData['data'][$this->groupCount]['options'][$this->attributeCount]['id'], $this->attributesAlreadyDone);
        while($alreadyDone){
            $this->attributeCount++;
            if($this->attributeCount >= $this->attributeTotal) {
                $this->groupCount++;
                if($this->groupCount >= $this->groupTotal) return null;
                $this->attributeCount = 0;
                $this->attributeTotal = count($this->jsonData['data'][$this->groupCount]['options']);
            }
            $alreadyDone = in_array($this->jsonData['data'][$this->groupCount]['options'][$this->attributeCount]['id'], $this->attributesAlreadyDone);
        }
        $attribute = [];
        $att = $this->attributeRepository->findOneBy(array('sid' => $this->jsonData['data'][$this->groupCount]['options'][$this->attributeCount]['id']));
        if($att != null && $att->getSid() != $att->getCode()) {
            $attribute['code'] = $att->getCode();
        } else {
            $attribute['code'] = $this->jsonData['data'][$this->groupCount]['options'][$this->attributeCount]['id'];
        }

        $attribute['attributeType'] = 'pim_catalog_multiselect';
        $attribute['sid'] = $this->jsonData['data'][$this->groupCount]['options'][$this->attributeCount]['id'];
        $attribute['decimals_allowed'] = true;
        $attribute['localizable'] = true;
        $attribute['useable_as_grid_filter'] = false;
        $attribute['available_locales'] = 'en_US';
        $attribute['label-en_US'] = $this->jsonData['data'][$this->groupCount]['options'][$this->attributeCount]['name'];
        $attribute['group'] = 'shopware_attributes';
        $attribute['unique'] = false;
        $attribute['required'] = false;
        $attribute['scopable'] = true;
        $attribute['negative_allowed'] = false;
        $this->attributesAlreadyDone[] = $this->jsonData['data'][$this->groupCount]['options'][$this->attributeCount]['id'];
        $this->attributeCount++;
        return $attribute;
    }

    protected function importImage() {
        $attribute = [];
        $attribute['code'] = "image";
        $attribute['attributeType'] = 'pim_catalog_image';
        $attribute['localizable'] = false;
        $attribute['useable_as_grid_filter'] = true;
        $attribute['available_locales'] = 'en_US';
        $attribute['label-en_US'] = "Product Image";
        $attribute['group'] = 'shopware_attributes';
        $attribute['unique'] = false;
        $attribute['required'] = false;
        $attribute['scopable'] = true;
        $attribute['negative_allowed'] = false;
        return $attribute;
    }

    protected function importNameAttribute() {
        $attribute = [];
        $attribute['code'] = "name";
        $attribute['attributeType'] = 'pim_catalog_text';
        $attribute['localizable'] = true;
        $attribute['useable_as_grid_filter'] = true;
        $attribute['available_locales'] = 'en_US';
        $attribute['label-en_US'] = "Name";
        $attribute['group'] = 'shopware_attributes';
        $attribute['unique'] = false;
        $attribute['required'] = true;
        $attribute['scopable'] = true;
        $attribute['negative_allowed'] = false;
        return $attribute;
    }

    protected function importDescriptionAttribute() {
        $attribute = [];
        $attribute['code'] = "description";
        $attribute['attributeType'] = 'pim_catalog_textarea';
        $attribute['localizable'] = true;
        $attribute['useable_as_grid_filter'] = false;
        $attribute['available_locales'] = 'en_US';
        $attribute['label-en_US'] = "Description";
        $attribute['group'] = 'shopware_attributes';
        $attribute['unique'] = false;
        $attribute['required'] = false;
        $attribute['scopable'] = true;
        $attribute['negative_allowed'] = false;
        return $attribute;
    }

    protected function importPriceAttribute() {
        $attribute = [];
        $attribute['code'] = "price";
        $attribute['attributeType'] = 'pim_catalog_price_collection';
        $attribute['decimals_allowed'] = true;
        $attribute['useable_as_grid_filter'] = true;
        $attribute['label-en_US'] = "Price";
        $attribute['group'] = 'shopware_attributes';
        $attribute['unique'] = false;
        $attribute['required'] = false;
        $attribute['scopable'] = true;
        $attribute['negative_allowed'] = false;
        return $attribute;
    }

    protected function importShortDescription() {
        $attribute = [];
        $attribute['code'] = "short_description";
        $attribute['attributeType'] = 'pim_catalog_text';
        $attribute['localizable'] = true;
        $attribute['useable_as_grid_filter'] = false;
        $attribute['available_locales'] = 'en_US';
        $attribute['label-en_US'] = "Short Description";
        $attribute['group'] = 'shopware_attributes';
        $attribute['unique'] = false;
        $attribute['required'] = false;
        $attribute['scopable'] = true;
        $attribute['negative_allowed'] = false;
        return $attribute;
    }

    protected function importPseudoSales() {
        $attribute = [];
        $attribute['code'] = "pseudo_sales";
        $attribute['attributeType'] = 'pim_catalog_boolean';
        $attribute['localizable'] = false;
        $attribute['useable_as_grid_filter'] = true;
        $attribute['available_locales'] = 'en_US';
        $attribute['label-en_US'] = "Pseudo sales";
        $attribute['group'] = 'shopware_attributes';
        $attribute['unique'] = false;
        $attribute['required'] = false;
        $attribute['scopable'] = true;
        $attribute['negative_allowed'] = false;
        return $attribute;
    }

    protected function importHighlight() {
        $attribute = [];
        $attribute['code'] = "highlight";
        $attribute['attributeType'] = 'pim_catalog_boolean';
        $attribute['localizable'] = false;
        $attribute['available_locales'] = 'en_US';
        $attribute['useable_as_grid_filter'] = true;
        $attribute['label-en_US'] = "Highlight";
        $attribute['group'] = 'shopware_attributes';
        $attribute['unique'] = false;
        $attribute['required'] = false;
        $attribute['scopable'] = true;
        $attribute['negative_allowed'] = false;
        return $attribute;
    }

    protected function importKeywords() {
        $attribute = [];
        $attribute['code'] = "keywords";
        $attribute['attributeType'] = 'pim_catalog_text';
        $attribute['localizable'] = true;
        $attribute['useable_as_grid_filter'] = false;
        $attribute['available_locales'] = 'en_US';
        $attribute['label-en_US'] = "Keywords";
        $attribute['group'] = 'shopware_attributes';
        $attribute['unique'] = false;
        $attribute['required'] = false;
        $attribute['scopable'] = true;
        $attribute['negative_allowed'] = false;
        return $attribute;
    }

    protected function importMetaTitle() {
        $attribute = [];
        $attribute['code'] = "meta_title";
        $attribute['attributeType'] = 'pim_catalog_text';
        $attribute['localizable'] = true;
        $attribute['useable_as_grid_filter'] = false;
        $attribute['available_locales'] = 'en_US';
        $attribute['label-en_US'] = "Meta title";
        $attribute['group'] = 'shopware_attributes';
        $attribute['unique'] = false;
        $attribute['required'] = false;
        $attribute['scopable'] = true;
        $attribute['negative_allowed'] = false;
        return $attribute;
    }

    protected function importPriceGroupActive() {
        $attribute = [];
        $attribute['code'] = "pricegroup_active";
        $attribute['attributeType'] = 'pim_catalog_boolean';
        $attribute['localizable'] = false;
        $attribute['useable_as_grid_filter'] = false;
        $attribute['available_locales'] = 'en_US';
        $attribute['label-en_US'] = "Price group active";
        $attribute['group'] = 'shopware_attributes';
        $attribute['unique'] = false;
        $attribute['required'] = false;
        $attribute['scopable'] = true;
        $attribute['negative_allowed'] = false;
        return $attribute;
    }

    protected function importNotification() {
        $attribute = [];
        $attribute['code'] = "notification";
        $attribute['attributeType'] = 'pim_catalog_boolean';
        $attribute['localizable'] = false;
        $attribute['available_locales'] = 'en_US';
        $attribute['label-en_US'] = "E-Mail notification";
        $attribute['useable_as_grid_filter'] = true;
        $attribute['group'] = 'shopware_attributes';
        $attribute['unique'] = false;
        $attribute['required'] = false;
        $attribute['scopable'] = true;
        $attribute['negative_allowed'] = false;
        return $attribute;
    }

    protected function importInStock() {
        $attribute = [];
        $attribute['code'] = "in_stock";
        $attribute['attributeType'] = 'pim_catalog_number';
        $attribute['localizable'] = false;
        $attribute['available_locales'] = 'en_US';
        $attribute['label-en_US'] = "In stock";
        $attribute['useable_as_grid_filter'] = true;
        $attribute['group'] = 'shopware_attributes';
        $attribute['unique'] = false;
        $attribute['required'] = false;
        $attribute['scopable'] = true;
        $attribute['negative_allowed'] = false;
        return $attribute;
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
    public function getJsonData()
    {
        return $this->jsonData;
    }

    /**
     * @param mixed $jsonData
     */
    public function setJsonData($jsonData)
    {
        $this->jsonData = $jsonData;
    }

    /**
     * @return mixed
     */
    public function getGroupCount()
    {
        return $this->groupCount;
    }

    /**
     * @param mixed $groupCount
     */
    public function setGroupCount($groupCount)
    {
        $this->groupCount = $groupCount;
    }

    /**
     * @return mixed
     */
    public function getAttributeCount()
    {
        return $this->attributeCount;
    }

    /**
     * @param mixed $attributeCount
     */
    public function setAttributeCount($attributeCount)
    {
        $this->attributeCount = $attributeCount;
    }

    /**
     * @return mixed
     */
    public function getGroupTotal()
    {
        return $this->groupTotal;
    }

    /**
     * @param mixed $groupTotal
     */
    public function setGroupTotal($groupTotal)
    {
        $this->groupTotal = $groupTotal;
    }

    /**
     * @return mixed
     */
    public function getAttributeTotal()
    {
        return $this->attributeTotal;
    }

    /**
     * @param mixed $attributeTotal
     */
    public function setAttributeTotal($attributeTotal)
    {
        $this->attributeTotal = $attributeTotal;
    }

    public function getConfigurationFields()
    {
        return [
            'apiKey' => [
                'options' => [
                    'label' => 'API-Key',
                    'help'  => 'pim_connector.import.filePath.help'
                ]
            ],
            'userName' => [
                'options' => [
                    'label' => 'Username',
                    'help'  => 'help'
                ]
            ],
            'url' => [
                'options' => [
                    'label' => 'URL',
                    'help'  => 'help'
                ]
            ]
        ];
    }
}