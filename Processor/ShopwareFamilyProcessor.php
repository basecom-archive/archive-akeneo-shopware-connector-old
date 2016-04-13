<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Processor;


use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Serializer\ShopwareFamilySerializer;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;

class ShopwareFamilyProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var ShopwareFamilySerializer */
    protected $serializer;

    /** @var LocaleManager */
    protected $localeManager;

    protected $locale;

    /**
     * ShopwareFamilyProcessor constructor.
     * @param ShopwareFamilySerializer $serializer
     */
    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
        $this->serializer = new ShopwareFamilySerializer();
    }

    public function process($item)
    {
        return $item;
        $item->setLocale($this->localeManager->getActiveCodes()[$this->locale]);
        return $this->serializer->serialize($item, $this->locale);
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