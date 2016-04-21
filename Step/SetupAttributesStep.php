<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Step;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Basecom\Bundle\ShopwareConnectorBundle\Handler\AttributeSetupHandler;

class SetupAttributesStep extends \Akeneo\Component\Batch\Step\AbstractStep
{
    /** @var AttributeSetupHandler */
    protected $handler;

    protected function doExecute(\Akeneo\Component\Batch\Model\StepExecution $stepExecution)
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

    public function setConfiguration(array $config)
    {
        foreach ($this->getConfigurableStepElements() as $stepElement) {
            if ($stepElement instanceof AbstractConfigurableStepElement) {
                $stepElement->setConfiguration($config);
            }
        }
    }

    // these getter / setter are required to allow to configure from form and execute
    public function getHandler()
    {
        return $this->handler;
    }

    public function setHandler(AttributeSetupHandler $handler)
    {
        $this->handler= $handler;
    }

    // step items which are configurable with the job edit form
    public function getConfigurableStepElements()
    {
        return array('handler' => $this->getHandler());
    }
}