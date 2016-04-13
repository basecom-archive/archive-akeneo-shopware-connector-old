<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Reader;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;

class ShopwareProductReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $url;

    protected $jsonData;

    protected $count;

    protected $total;

    protected $client;

    public function read()
    {
        if($this->jsonData === null) {
            echo "Product import...\n";
            $this->client = new ApiClient($this->url, $this->userName, $this->apiKey);
            $this->jsonData = $this->client->get('articles');
            $this->count = 1;
            $this->total = $this->jsonData['total'];
        }
        if($this->count > $this->total) return null;
        echo "\nproduct ".$this->count . " of " . $this->total . "\n";
        $this->jsonData = $this->client->get('articles/'.$this->count);

        echo (string)$this->jsonData['data']['mainDetail']['number']."\n";
        $article = [];

        $categories = "";
        $categoryCount = 0;
        foreach($this->jsonData['data']['categories'] as $category) {
            if($categoryCount != 0) {
                $categories .= ", ";
            }
            $categories .= $category['id'];
            $categoryCount++;
        }
        $article['sku'] = (string)$this->jsonData['data']['mainDetail']['number'];

        $article['name-en_US'] = (string)$this->jsonData['data']['name'];
        $article['family'] = (string)$this->jsonData['data']['propertyGroup']['id'];
        $article['categories'] = $categories;
        $article['description-en_US'] = $this->jsonData['data']['descriptionLong'];
        $article['enabled'] = (string)$this->jsonData['data']['active'];
        $article['short_description-en_US'] = $this->jsonData['data']['description'];
        $article['pseudo_sales'] = $this->jsonData['data']['pseudoSales'];
        $article['highlight'] = $this->jsonData['data']['highlight'];
        $article['keywords-en_US'] = (string)$this->jsonData['data']['keywords'];
        $article['meta_title-en_US'] = (string)$this->jsonData['data']['metaTitle'];
        $article['pricegroup_active'] = $this->jsonData['data']['priceGroupActive'];
        $article['notification'] = $this->jsonData['data']['notification'];
        $article['in_stock'] = $this->jsonData['data']['mainDetail']['inStock'];
        foreach($this->jsonData['data']['propertyValues'] as $propertyValue) {
            $array = [];
            array_push($array,$this->createCode((string)$propertyValue['id']));
            if(isset($article[(string) $propertyValue['optionId']."-en_US"])) {
                $article[(string) $propertyValue['optionId']."-en_US"] .= ",".$this->createCode((string)$propertyValue['value']);
            } else {
                $article[(string) $propertyValue['optionId']."-en_US"] = $this->createCode((string)$propertyValue['value']);
            }
            //echo $this->createCode((string)$propertyValue['id']).": ".$article[(string) $propertyValue['optionId']."-en_US"]."\n";
        }
        $prices = "";
        $pricesCount = 0;
        foreach($this->jsonData['data']['mainDetail']['prices'] as $price) {
            if($pricesCount > 0) $prices .= ",";
            $prices .= (string)$price['price']." EUR";

            $pricesCount++;
        }
        $article['price'] = $prices;

        $this->count++;
        return $article;
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