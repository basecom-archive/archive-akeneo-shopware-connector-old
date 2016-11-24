<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Writer;

use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Posts all provided products to shopware via Rest API
 *
 * Class ShopwareProductWriter
 * @package Basecom\Bundle\ShopwareConnectorBundle\Writer
 */
class ShopwareProductWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $url;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @var ArrayCollection
     */
    protected $attributes;

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        $apiClient = new ApiClient(
            $jobParameters->get('url'),
            $jobParameters->get('userName'),
            $jobParameters->get('apiKey')
        );


        $response = $apiClient->put('articles/', $items);

        if(false === $response) {
            throw new \Exception('An error occured while upload a product to Shopware');
        }
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
