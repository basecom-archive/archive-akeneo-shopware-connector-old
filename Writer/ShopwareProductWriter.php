<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Writer;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Doctrine\Common\Collections\ArrayCollection;

class ShopwareProductWriter extends AbstractConfigurableStepElement implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $url;

    /** @var StepExecution */
    protected $stepExecution;

    protected $apiClient;

    /**
     * @var ArrayCollection
     */
    protected $attributes;


    public function write(array $items)
    {
        $this->apiClient = new ApiClient($this->url, $this->userName, $this->apiKey);
        $this->apiClient->put('articles/',$items);

//        foreach($items as $item) {
//            $this->apiClient->post('articles/'.$number.'?useNumberAsId=true', $item);
//        }
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
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
    public function getApiClient()
    {
        return $this->apiClient;
    }

    /**
     * @param mixed $apiClient
     */
    public function setApiClient($apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @return ArrayCollection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param ArrayCollection $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
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