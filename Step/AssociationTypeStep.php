<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Step;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Step\AbstractStep;
use Basecom\Bundle\ShopwareConnectorBundle\Handler\AssociationTypeHandler;

class AssociationTypeStep extends AbstractStep
{
    /** @var AssociationTypeHandler */
    protected $handler;

    protected function doExecute(StepExecution $stepExecution)
    {
        $this->handler->setStepExecution($stepExecution);
        $this->handler->execute();
    }

    public function getConfiguration()
    {
        $configuration = array();
        foreach ($this->getConfigurableStepElements() as $stepElement) {
            if ($stepElement instanceof AbstractConfigurableStepElement) {
                foreach ($stepElement->getConfiguration() as $key => $value) {
                    if (!isset($configuration[$key]) || $value) {
                        $configuration[$key] = $value;
                    }
                }
            }
        }

        return $configuration;
    }

    /**
     * @return AssociationTypeHandler
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param AssociationTypeHandler $handler
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

    public function setConfiguration(array $config)
    {
        foreach ($this->getConfigurableStepElements() as $stepElement) {
            if ($stepElement instanceof AbstractConfigurableStepElement) {
                $stepElement->setConfiguration($config);
            }
        }
    }

    public function getConfigurableStepElements()
    {
        return array('handler' => $this->getHandler());
    }
}