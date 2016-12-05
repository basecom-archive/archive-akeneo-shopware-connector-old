<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Step;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\AbstractStep;
use Basecom\Bundle\ShopwareConnectorBundle\Handler\AttributeSetupHandler;

/**
 * Class SetupAttributesStep.
 */
class SetupAttributesStep extends AbstractStep
{
    /** @var AttributeSetupHandler */
    protected $handler;

    /**
     * @param StepExecution $stepExecution
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        $this->handler->setStepExecution($stepExecution);
        $this->handler->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        $configuration = [];
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
     * {@inheritdoc}
     */
    public function setConfiguration(array $config)
    {
        foreach ($this->getConfigurableStepElements() as $stepElement) {
            if ($stepElement instanceof AbstractConfigurableStepElement) {
                $stepElement->setConfiguration($config);
            }
        }
    }

    // these getter / setter are required to allow to configure from form and execute
    /**
     * @return AttributeSetupHandler
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param AttributeSetupHandler $handler
     */
    public function setHandler(AttributeSetupHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * step items which are configurable with the job edit form.
     *
     * @return array
     */
    public function getConfigurableStepElements()
    {
        return ['handler' => $this->getHandler()];
    }
}
