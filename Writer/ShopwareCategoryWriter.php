<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Writer;

use Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository\CategoryRepository;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Category;
use Doctrine\ORM\EntityManager;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * Posts all provided categories to shopware via Rest API
 *
 * Class ShopwareCategoryWriter
 * @package Basecom\Bundle\ShopwareConnectorBundle\Writer
 */
class ShopwareCategoryWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $url;

    /** @var string */
    protected $locale;

    /** @var CategoryRepository $categoryRepository */
    protected $categoryRepository;

    /** @var EntityManager $entityManager */
    protected $entityManager;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * ShopwareCategoryWriter constructor.
     *
     * @param CategoryRepository        $categoryRepository
     * @param EntityManager             $entityManager
     * @param LocaleRepositoryInterface $localeManager
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        EntityManager $entityManager,
        LocaleRepositoryInterface $localeManager
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
        $this->localeRepository = $localeManager;
    }

    /**
     * posts categories to Shopware
     *
     * @param Category[] $items
     */
    public function write(array $items)
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $locale = $jobParameters->get('locale');

        $apiClient = new ApiClient(
            $jobParameters->get('url'),
            $jobParameters->get('userName'),
            $jobParameters->get('apiKey')
        );


        /**
         * @var Category $item
         */
        foreach ($items as $item) {
            $item->setLocale($locale);
            $parent = 1;
            if (null !== $item->getParent() && null !== $item->getParent()->getSwId()) {
                $parent = $item->getParent()->getSwId();
            }
            $swCategory = [
                'name'             => $item->getLabel(),
                'parentId'         => $parent,
                'active'           => true,
                'blog'             => false,
                'showFilterGroups' => true,
            ];
            if (null !== $item->getSwId()) {
                if (null == $apiClient->put('categories/' . $item->getSwId(), $swCategory)) {
                    $category = $apiClient->post('categories', $swCategory);
                    $item->setSwId($category['data']['id']);
                    $this->stepExecution->incrementSummaryInfo('write');
                } else {
                    $item->setSwId(null);
                }

                $this->entityManager->persist($item);
            } else {
                $category = $apiClient->post('categories', $swCategory);
                $item->setSwId($category['data']['id']);
                $this->entityManager->persist($item);
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @return LocaleRepositoryInterface
     */
    public function getLocaleRepository()
    {
        return $this->localeRepository;
    }

    /**
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function setLocaleRepository($localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
