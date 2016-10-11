<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Step;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\AbstractStep;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;

/**
 * Recieves Shopwares additional attribute fields
 * and writes them into a csv file
 *
 * Class AttributeSetupHandler
 * @package Basecom\Bundle\ShopwareConnectorBundle\Handler
 */
class AttributeSetup extends AbstractStep
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
        $parameters = $stepExecution->getJobParameters();
        $url = $parameters->get('url');
        $userName= $parameters->get('userName');
        $apiKey = $parameters->get('apiKey');
        $client = new ApiClient($url, $userName, $apiKey);
        if($client) {
            $jsonData = $client->get('attributes');
            $fp = fopen(__DIR__ . '/../Resources/config/additional_attributes.csv', 'w');
            foreach ($jsonData['data'] as $data) {
                fputcsv($fp, $data, ";");
            }
            fclose($fp);
            return true;
        } else {
            return false;
        }
    }
}