<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Reader;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;

class ShopwareFamilyReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $url;

    protected $jsonData;

    protected $count;

    public function read()
    {
        if($this->jsonData === null) {
            echo "Family import...\n";
            $client = new ApiClient($this->url, $this->userName, $this->apiKey);
            $this->jsonData = $client->get('propertyGroups');
            $this->count=0;
        }

        $family = [];

        $total = $this->jsonData['total'];
        if($this->count >= $total) return null;
        $family['code'] = $this->jsonData['data'][$this->count]['id'];
        $family['label-en_US'] = $this->jsonData['data'][$this->count]['name'];
        $attributes = "";
        $attributeCount = 0;
        foreach($this->jsonData['data'][$this->count]['options'] as $option)
        {
            if($attributeCount > 0) $attributes .= ",";
            $attributes .= $option['id'];
            $attributeCount++;
        }
        $attributes .= ",name,description,short_description,meta_title,pseudo_sales,highlight,pricegroup_active,notification,in_stock,image,price";
        $family['attributes'] = $attributes;
        $family['attribute_as_label'] = "name";
        $this->count++;
        return $family;
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
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param mixed $count
     */
    public function setCount($count)
    {
        $this->count = $count;
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
                    'help'  => 'HELP!'
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