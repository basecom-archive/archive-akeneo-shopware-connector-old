<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Reader;

use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Category;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Model\Product;

/**
 * Fetches all products for a category and hands them over to the processor
 *
 * Class ShopwareProductExportReader
 * @package Basecom\Bundle\ShopwareConnectorBundle\Reader
 */
class ShopwareProductExportReader implements
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var \ArrayIterator */
    protected $results;
    /**
     * @var ChannelRepositoryInterface
     */
    private $channelRepository;

    /**
     * ShopwareProductExportReader constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ChannelRepositoryInterface $channelRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        ChannelRepositoryInterface $channelRepository
    )
    {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->channelRepository = $channelRepository;
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
        /** @var Category $rootCategory */
        $rootCategory = $this->categoryRepository->findOneByIdentifier($this->stepExecution->getJobParameters()->get('rootCategory'));
        $categories = $rootCategory->getChildren();
        $channel = $this->channelRepository->findOneByIdentifier($this->stepExecution->getJobParameters()->get('channel'));
        $qb = $this->productRepository->buildByChannelAndCompleteness($channel);
        $products = $qb->getQuery()->execute();
        /** @var Category $category */
        foreach ($categories as $category) {
            /** @var Product $product */
            foreach ($products as $product) {
                if (in_array($category->getCode(), $product->getCategoryCodes())) {
                    array_push($products, $product);
                }
            }
        }

        return new \ArrayIterator($products);
    }
}
