<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Handler;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;

/**
 * Recieves Shopwares additional attribute fields
 * and writes them into a csv file
 *
 * Class AttributeSetupHandler
 * @package Basecom\Bundle\ShopwareConnectorBundle\Handler
 */
class AttributeSetupHandler extends AbstractConfigurableStepElement implements StepExecutionAwareInterface
{
    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * Shopwares API-Key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Shopwares API-Username
     *
     * @var string
     */
    protected $userName;

    /**
     * the Shopware API-URL
     *
     * @var string
     */
    protected $url;

    /**
     * Gets Shopwares additional attribute fields via API call
     * and writes them to a csv file
     */
    public function execute()
    {
        $client = new ApiClient($this->url, $this->userName, $this->apiKey);
        $jsonData = $client->get('attributes');
        $fp = fopen(__DIR__ . '/../Resources/config/additional_attributes.csv', 'w');
        foreach ($jsonData['data'] as $data) {
            fputcsv($fp, $data, ";");
        }
        fclose($fp);
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Returns the configuration Fields for the job
     *
     * @return array
     */
    public function getConfigurationFields()
    {
        return [
            'apiKey'   => [
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
            'url'      => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.url.label',
                    'help'  => 'basecom_shopware_connector.export.url.help'
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
