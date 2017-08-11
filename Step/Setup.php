<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Step;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\AbstractStep;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;

/**
 * @author Amir El Sayed <elsayed@basecom.de>
 *
 * Receives Shopwares additional attribute fields
 * and writes them into a csv file
 *
 * Class AttributeSetupHandler
 * @package Basecom\Bundle\ShopwareConnectorBundle\Handler
 */
class Setup extends AbstractStep
{
    /**
     * Extension point for subclasses to execute business logic. Subclasses should set the {@link ExitStatus} on the
     * {@link StepExecution} before returning.
     *
     * Do not catch exception here. It will be correctly handled by the execute() method.
     *
     * @param StepExecution $stepExecution the current step context
     *
     * @return bool
     * @throws \Exception
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        $jobParameters = $stepExecution->getJobParameters();

        $apiClient = new ApiClient(
            $jobParameters->get('url'),
            $jobParameters->get('userName'),
            $jobParameters->get('apiKey')
        );

        if($apiClient) {
            return $this->writeAttributes($apiClient) && $this->writeShops($apiClient);
        } else {
            return false;
        }
    }

    /**
     * @param ApiClient $apiClient
     *
     * @return bool
     */
    protected function writeAttributes($apiClient)
    {
        $jsonData = $apiClient->get('attributes');
        $fp = fopen(__DIR__ . '/../Resources/config/additional_attributes.csv', 'w');
        foreach ($jsonData['data'] as $data) {
            fputcsv($fp, $data, ";");
        }
        fclose($fp);

        return true;
    }

    /**
     * @param ApiClient $apiClient
     *
     * @return bool
     */
    protected function writeShops($apiClient)
    {
        $jsonData = $apiClient->get('shops');
        $fp = fopen(__DIR__ . '/../Resources/config/shops.csv', 'w');
        foreach ($jsonData['data'] as $data) {
            fputcsv($fp, $data, ";");
        }
        fclose($fp);

        return true;
    }
}
