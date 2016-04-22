<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Reader;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Category;

/**
 * Fetches all categories of a tree and hands them over to the processor
 *
 * Class ShopwareCategoryExportReader
 * @package Basecom\Bundle\ShopwareConnectorBundle\Reader
 */
class ShopwareCategoryExportReader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var bool Checks if all categories are sent to the processor */
    protected $isExecuted = false;

    /** @var \ArrayIterator */
    protected $results;

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
    public function getConfigurationFields()
    {
        return [
            'rootCategory' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.rootCategory.label',
                    'help'  => 'basecom_shopware_connector.export.rootCategory.help'
                ]
            ],
        ];
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
    protected function getResults()
    {
        /** @var Category $category */
        $category = $this->categoryRepository->findOneByIdentifier($this->rootCategory);
        $categories = $this->categoryRepository->findBy(array('root' => $category->getRoot()));
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
