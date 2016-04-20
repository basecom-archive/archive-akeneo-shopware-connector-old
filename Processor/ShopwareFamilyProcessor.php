<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Processor;



use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

class ShopwareFamilyProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var LocaleRepositoryInterface */
    protected $localeManager;

    protected $locale;

    /**
     * ShopwareFamilyProcessor constructor.
     */
    public function __construct(LocaleRepositoryInterface $localeManager)
    {
        $this->localeManager = $localeManager;
    }

    public function process($item)
    {
        return $item;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getConfigurationFields()
    {
        return array();
    }
}