<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Reader;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository\CategoryRepository;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\ProductRepository;

class ShopwareProductImageReader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var ProductRepository */
    protected $productRepository;

    /** @var CategoryRepository */
    protected $categoryRepository;

    /** @var \ArrayIterator */
    protected $results;

    /** @var bool Checks if all attributes are sent to the processor */
    protected $isExecuted = false;

    /**
     * ShopwareProductExportReader constructor.
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository, CategoryRepository $categoryRepository)
    {
        $this->productRepository  = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function read()
    {
        echo "ProductImageReader...\n";
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

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * @return \ArrayIterator
     */
    public function getResults()
    {
        $categories = $this->categoryRepository->findAll();
        $products = array();
        /** @var Category $category */
        foreach($categories as $category) {
            if($category->getSid() != null || $category->getParent() != null && $category->getParent()->getSid() != null) {
                foreach($this->productRepository->findAllForCategory($category) as $product) {
                    array_push($products, $product);
                }
            }
        }
        return new \ArrayIterator($products);
    }
}