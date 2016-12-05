<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Reader;

use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;

/**
 * Fetches all Families and hands them over to the processor.
 *
 * Class ShopwareFamilyExportReader
 */
class ShopwareFamilyExportReader implements
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var \ArrayIterator */
    protected $results;

    /**
     * ShopwareFamilyExportReader constructor.
     *
     * @param FamilyRepositoryInterface $familyRepository
     */
    public function __construct(FamilyRepositoryInterface $familyRepository)
    {
        $this->familyRepository = $familyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->results) {
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
     * @return \ArrayIterator
     */
    public function getResults()
    {
        return new \ArrayIterator($this->familyRepository->getFullFamilies());
    }
}
