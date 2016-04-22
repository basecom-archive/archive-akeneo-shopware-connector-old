<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Reader;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\FamilyRepository;

/**
 * Fetches all Families and hands them over to the processor
 *
 * Class ShopwareFamilyExportReader
 * @package Basecom\Bundle\ShopwareConnectorBundle\Reader
 */
class ShopwareFamilyExportReader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var FamilyRepository */
    protected $familyRepository;

    /** @var \ArrayIterator */
    protected $results;

    /** @var bool Checks if all attributes are sent to the processor */
    protected $isExecuted = false;

    /**
     * ShopwareFamilyExportReader constructor.
     * @param FamilyRepository $familyRepository
     */
    public function __construct(FamilyRepository $familyRepository)
    {
        $this->familyRepository = $familyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->isExecuted) {
            $this->isExecuted = true;
            $this->results = $this->getResults();
        }

        if (null !== $result = $this->results->current()) {
            $this->results->next();
            $this->stepExecution->incrementSummaryInfo('read');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }

    /**
     * @return \ArrayIterator
     */
    public function getResults()
    {
        return new \ArrayIterator($this->familyRepository->getFullFamilies());
    }
}
