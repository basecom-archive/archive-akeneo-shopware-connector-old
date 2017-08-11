<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Reader;

use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Category;

/**
 * @author Amir El Sayed <elsayed@basecom.de>
 *
 * Fetches all categories of a tree and hands them over to the processor
 *
 * Class ShopwareCategoryExportReader
 * @package Basecom\Bundle\ShopwareConnectorBundle\Reader
 */
class ShopwareCategoryExportReader implements
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var \ArrayIterator */
    protected $results;

    /** @var string */
    protected $rootCategory;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->results) {
            $jobParameters = $this->stepExecution->getJobParameters();
            $this->results = $this->getResults($jobParameters->get('rootCategory'));
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
    protected function getResults($rootCategory)
    {
        /** @var Category $category */
        $category = $this->categoryRepository->findOneByIdentifier($rootCategory);
        $categories = $this->categoryRepository->findBy(['root' => $category->getRoot()]);

        return new \ArrayIterator($categories);
    }

    /**
     * @return mixed
     */
    public function getRootCategory()
    {
        return $this->rootCategory;
    }

    /**
     * @param mixed $rootCategory
     */
    public function setRootCategory($rootCategory)
    {
        $this->rootCategory = $rootCategory;
    }
}
