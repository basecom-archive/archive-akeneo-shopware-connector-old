<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Serializer\ShopwareCategorySerializer;

class ShopwareCategoryProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var ShopwareCategorySerializer */
    protected $serializer;

    /**
     * ShopwareCategoryProcessor constructor.
     * @param ShopwareCategorySerializer $serializer
     */
    public function __construct()
    {
        $this->serializer = new ShopwareCategorySerializer();
    }

    public function process($item)
    {
        echo "\nCategory Processor...\n";
        return $item;
        return $this->serializer->serialize($item);
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function getConfigurationFields()
    {
        return array();
    }
}