<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * processes the family for the export to shopware
 *
 * Class ShopwareFamilyProcessor
 * @package Basecom\Bundle\ShopwareConnectorBundle\Processor
 */
class ShopwareFamilyProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param mixed $item
     *
     * @return mixed
     */
    public function process($item)
    {
        return $item;
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
