<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Reader;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;

class ShopwareAttributeOptionReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $url;

    protected $jsonData;

    protected $client;

    protected $articleCount;

    protected $attributeOptionCount;

    protected $total;

    protected $attributeOptionsAlreadyDone;

    public function read()
    {
        if($this->jsonData === null) {
            echo "Attribute Options import...";
            $this->client = new ApiClient($this->url, $this->userName, $this->apiKey);
            $this->jsonData = $this->client->get('articles');
            $this->articleCount = 1;
            $this->attributeOptionCount = 0;
            $this->total = $this->jsonData['total'];
            $this->attributeOptionsAlreadyDone = [];
        }
        $this->jsonData = $this->client->get('articles/'.$this->articleCount);
        $attributeOptionTotal = count($this->jsonData['data']['propertyValues']);
        if($this->attributeOptionCount >= $attributeOptionTotal) {
            $this->articleCount++;
            if($this->articleCount > $this->total) return null;
            $this->jsonData = $this->client->get('articles/'.$this->articleCount);
            $this->attributeOptionCount = 0;
            $attributeOptionTotal = count($this->jsonData['data']['propertyValues']);
        }

        $alreadyDone = in_array($this->jsonData['data']['propertyValues'][$this->attributeOptionCount]['id'], $this->attributeOptionsAlreadyDone);
        while($alreadyDone){
            $this->attributeOptionCount++;
            if($this->attributeOptionCount >= $attributeOptionTotal) {
                $this->articleCount++;
                if($this->articleCount > $this->total) return null;
                $this->jsonData = $this->client->get('articles/'.$this->articleCount);
                $this->attributeOptionCount = 0;
                $attributeOptionTotal = count($this->jsonData['data']['propertyValues']);
            }
            $alreadyDone = in_array($this->jsonData['data']['propertyValues'][$this->attributeOptionCount]['id'], $this->attributeOptionsAlreadyDone);
        }
        $attributeOption = [];
        $attributeOption['attribute'] = $this->jsonData['data']['propertyValues'][$this->attributeOptionCount]['optionId'];
        $attributeOption['code'] = $this->createCode($this->jsonData['data']['propertyValues'][$this->attributeOptionCount]['value']);
        $attributeOption['sort_order'] = $this->jsonData['data']['propertyValues'][$this->attributeOptionCount]['position'];
        //$attributeOption['label-de_DE'] = $this->jsonData['data']['propertyValues'][$this->attributeOptionCount]['value'];
        $attributeOption['label-en_US'] = $this->jsonData['data']['propertyValues'][$this->attributeOptionCount]['value'];
        $this->attributeOptionsAlreadyDone[] = $this->jsonData['data']['propertyValues'][$this->attributeOptionCount]['id'];
        $this->attributeOptionCount++;
        return $attributeOption;
    }

    public function createCode($label) {
        $code = str_replace("&", "_", strtolower($label));
        $code = str_replace(" ", "", $code);
        $code = str_replace("ö", "oe", $code);
        $code = str_replace("Ö", "oe", $code);
        $code = str_replace("ä", "ae", $code);
        $code = str_replace("Ä", "ae", $code);
        $code = str_replace("ü", "ue", $code);
        $code = str_replace("Ü", "ue", $code);
        $code = str_replace("ß", "ss", $code);
        $code = str_replace(".", "_", $code);
        $code = str_replace("-", "_", $code);
        $code = str_replace("/", "_", $code);
        $code = str_replace(",", "_", $code);
        $code = str_replace(";", "_", $code);
        $code = str_replace("®", "", $code);

        return $code;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getArticleCount()
    {
        return $this->articleCount;
    }

    /**
     * @param mixed $articleCount
     */
    public function setArticleCount($articleCount)
    {
        $this->articleCount = $articleCount;
    }

    /**
     * @return mixed
     */
    public function getAttributeOptionCount()
    {
        return $this->attributeOptionCount;
    }

    /**
     * @param mixed $attributeOptionCount
     */
    public function setAttributeOptionCount($attributeOptionCount)
    {
        $this->attributeOptionCount = $attributeOptionCount;
    }

    /**
     * @return mixed
     */
    public function getAttributeOptionsAlreadyDone()
    {
        return $this->attributeOptionsAlreadyDone;
    }

    /**
     * @param mixed $attributeOptionsAlreadyDone
     */
    public function setAttributeOptionsAlreadyDone($attributeOptionsAlreadyDone)
    {
        $this->attributeOptionsAlreadyDone = $attributeOptionsAlreadyDone;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;
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