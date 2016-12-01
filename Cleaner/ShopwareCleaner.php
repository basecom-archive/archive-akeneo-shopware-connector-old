<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Cleaner;

use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\AbstractStep;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Repository\FileInfoRepository;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Repository\ProductRepository;
use Doctrine\ORM\Query\ResultSetMapping;
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
     *
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

    /**
     * @param StepExecution $stepExecution
     * @return bool
     */
    protected function cleanProducts(StepExecution $stepExecution)
    {
        $articles = $this->apiClient->get('articles/');
        if(!$articles || !$articles['success']) return false;

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

    /**
     * @param StepExecution $stepExecution
     * @return bool
     */
    protected function cleanProductMedia(StepExecution $stepExecution)
    {
        $media = $this->apiClient->get('media/');

        if(!$media || !$media['success']) return false;

        /**
         * Enterprise Query
         * SELECT fileinfo.swMediaId FROM akeneo_file_storage_file_info fileinfo LEFT JOIN pimee_product_asset_variation av ON fileinfo.id = av.file_info_id LEFT JOIN pimee_product_asset_reference ref ON av.reference_id = ref.id LEFT JOIN pim_catalog_product_value_asset valas ON valas.asset_id = ref.asset_id LEFT JOIN pim_catalog_product_value prodval ON prodval.id = valas.value_id LEFT JOIN pim_catalog_product prod ON prod.id = prodval.entity_id WHERE fileinfo.swMediaId IS NOT NULL AND prod.swProductId IS NOT NULL
         */
        $productMedia = $this->productRepository->findProductMediaWithSwId();
        if(!empty($productMedia)) {
            $productMedia = array_column($productMedia, 'swMediaId');
        }

        foreach($media['data'] as $singleMedia) {
            if(-1 === $singleMedia['albumId'] && !in_array($singleMedia['id'], $productMedia)) {
                $result = $this->apiClient->delete('media/'.$singleMedia['id']);

                if($result['success']) {
                    $stepExecution->incrementSummaryInfo('media deleted');
                }
            }
        }
    }
}