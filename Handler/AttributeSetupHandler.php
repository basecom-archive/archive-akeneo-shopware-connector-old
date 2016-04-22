<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Handler;

use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;

class AttributeSetupHandler extends \Akeneo\Component\Batch\Item\AbstractConfigurableStepElement implements \Akeneo\Component\Batch\Step\StepExecutionAwareInterface
{
    // ToDo: An alle Klassenvariabeln und Funktionen noch PHPDoc
    protected $stepExecution;

    protected $apiKey;

    protected $userName;

    protected $url;

    public function execute()
    {
        $client = new ApiClient($this->url, $this->userName, $this->apiKey);
        $jsonData = $client->get('attributes');
        $fp = fopen(__DIR__.'/../Resources/config/additional_attributes.csv', 'w');
        foreach($jsonData['data'] as $data) {
            fputcsv($fp, $data, ";");
        }
        fclose($fp);
    }

    public function setStepExecution(\Akeneo\Component\Batch\Model\StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function getConfigurationFields()
    {
        return [
            'apiKey' => [
                'options' => [
                    'label' => 'API-Key',
                    'help'  => 'API-Key'
                ]
            ],
            'userName' => [
                'options' => [
                    'label' => 'Username',
                    'help'  => 'Username'
                ]
            ],
            'url' => [
                'options' => [
                    'label' => 'URL',
                    'help'  => 'URL'
                ]
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param mixed $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param mixed $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}