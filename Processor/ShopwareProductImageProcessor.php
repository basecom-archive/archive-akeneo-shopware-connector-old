<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Serializer\ShopwareProductImageSerializer;

class ShopwareProductImageProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var ShopwareProductImageSerializer */
    protected $serializer;

    /**
     * ShopwareProductImageProcessor constructor.
     * @param ShopwareProductImageSerializer $serializer
     */
    public function __construct(ShopwareProductImageSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function process($item)
    {
        return $this->serializer->serialize($item);
    }

    public function getConfigurationFields()
    {
        return array();
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

}