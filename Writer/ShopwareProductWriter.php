<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Writer;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Posts all provided products to shopware via Rest API
 *
 * Class ShopwareProductWriter
 * @package Basecom\Bundle\ShopwareConnectorBundle\Writer
 */
class ShopwareProductWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $url;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @var ArrayCollection
     */
    protected $attributes;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(ProductRepository $productRepository, EntityManagerInterface $entityManager)
    {
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        $apiClient = new ApiClient(
            $jobParameters->get('url'),
            $jobParameters->get('userName'),
            $jobParameters->get('apiKey')
        );

        foreach($items as $item) {
            $response = $apiClient->get('variants/'.$item['mainDetail']['number'].'?useNumberAsId=true');

            if($response['success'] === false) {
                $response = $apiClient->post('articles/', $item);
            } else {
                $response = $apiClient->put('articles/'.$item['mainDetail']['number'].'?useNumberAsId=true', $item);
            }

            if($response['success']) {
                $product = $this->productRepository->findOneByIdentifier($item['mainDetail']['number']);
                $product->setSwProductId($response['data']['id']);
                $this->entityManager->persist($product);
            }
        }

        $this->entityManager->flush();
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
