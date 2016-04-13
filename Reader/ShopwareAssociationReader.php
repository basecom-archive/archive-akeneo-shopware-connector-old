<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Reader;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;

class ShopwareAssociationReader extends AbstractConfigurableStepElement implements ItemReaderInterface
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
            echo "Association import ...\n";
            $this->client = new ApiClient($this->url, $this->userName, $this->apiKey);
            $this->jsonData = $this->client->get('articles');
            $this->count = 1;
            $this->total = $this->jsonData['total'];
        }
        if($this->count > $this->total) return null;

        echo "\nAssociationproduct ".$this->count . " of " . $this->total . "\n";
        $this->jsonData = $this->client->get('articles/'.$this->count);
        $association = [];
        $association['sku'] = (string)$this->jsonData['data']['mainDetail']['number'];

        $similarities = "";
        $similarCount = 0;
        if($this->jsonData['data']['similar'] != null){
            foreach($this->jsonData['data']['similar'] as $similar) {
                if($similarCount > 0) {
                    $similarities .= ", ";
                }
                $similarJsonData = $this->client->get('articles/'.$similar['id']);
                $similarities .= (string)$similarJsonData['data']['mainDetail']['number'];
                $similarCount++;
            }
        }
        $association['similar-products'] = $similarities;
        echo "Similar: ".$similarities."\n";

        $relations = "";
        $relationCount = 0;
        if($this->jsonData['data']['related'] != null){
            foreach($this->jsonData['data']['related'] as $relation) {
                if($relationCount > 0) {
                    $relations .= ", ";
                }
                $relatedJsonData = $this->client->get('articles/'.$relation['id']);
                $relations .= (string)$relatedJsonData['data']['mainDetail']['number'];
                $relationCount++;
            }
        }
        $association['related-products'] = $relations;
        echo "Related: ".$relations."\n";
        $this->count++;
        return $association;
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