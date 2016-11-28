<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Cleaner;

use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\AbstractStep;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Repository\FileInfoRepository;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Repository\ProductRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ShopwareCleaner extends AbstractStep
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ApiClient
     */
    protected $apiClient;
    /**
     * @var FileInfoRepository
     */
    private $fileInfoRepository;

    /**
     * ShopwareCleaner constructor.
     * @param string $name
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobRepositoryInterface $jobRepository
     * @param ProductRepository $productRepository
     * @param FileInfoRepositoryInterface $fileInfoRepository
     */
    public function __construct($name, EventDispatcherInterface $eventDispatcher, JobRepositoryInterface $jobRepository, ProductRepository $productRepository, FileInfoRepositoryInterface $fileInfoRepository)
    {
        parent::__construct($name, $eventDispatcher, $jobRepository);
        $this->productRepository = $productRepository;
        $this->fileInfoRepository = $fileInfoRepository;
    }

    /**
     * Extension point for subclasses to execute business logic. Subclasses should set the {@link ExitStatus} on the
     * {@link StepExecution} before returning.
     *
     * Do not catch exception here. It will be correctly handled by the execute() method.
     *
     * @param StepExecution $stepExecution the current step context
     *
     * @throws \Exception
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        $jobParameters = $stepExecution->getJobParameters();

        $this->apiClient = new ApiClient(
            $jobParameters->get('url'),
            $jobParameters->get('userName'),
            $jobParameters->get('apiKey')
        );

        $this->cleanProducts($stepExecution);
        $this->cleanProductMedia($stepExecution);
    }

    protected function cleanProducts(StepExecution $stepExecution)
    {
        $articles = $this->apiClient->get('articles/');
        $articleIds = array_column($articles['data'], 'id');
        $productIdsToKeep = array_column($this->productRepository->findIdByNotInSwId($articleIds), 'swProductId');

        foreach($articleIds as $article)
        {
            if(!in_array($article, $productIdsToKeep)) {
                $result = $this->apiClient->delete('articles/'.$article);

                if($result['success']) {
                    $stepExecution->incrementSummaryInfo('product deleted');
                }
            }
        }
    }

    protected function cleanProductMedia(StepExecution $stepExecution)
    {
        $media = $this->apiClient->get('media/');
        $mediaIds = array_column($media['data'], 'id');
        $productMedia = $this->productRepository->findProductMediaWithSwId($mediaIds);
        if(!empty($productMedia)) {
            $productMedia = array_column($productMedia, 'swMediaId');
        }

        $mediaIdsToDelete = $this->fileInfoRepository->findMediaIdsNotInProducts($productMedia, $mediaIds);

        foreach($mediaIdsToDelete as $mediaToDelete)
        {
            $result = $this->apiClient->delete('media/'.$mediaToDelete['swMediaId']);

            if($result['success']) {
                $stepExecution->incrementSummaryInfo('media deleted');
            }
        }
    }
}