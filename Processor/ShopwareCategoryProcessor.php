<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * processes the category for the export to shopware
 *
 * Class ShopwareCategoryProcessor
 * @package Basecom\Bundle\ShopwareConnectorBundle\Processor
 */
class ShopwareCategoryProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /**
     * processes the category for the export
     *
     * @param mixed $item
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

    /**
     * @return array
     */
    public function getConfigurationFields()
    {
        return array();
    }
}
