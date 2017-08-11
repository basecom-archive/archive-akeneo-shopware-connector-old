<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Writer;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Family;
use Doctrine\ORM\EntityManager;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * @author Amir El Sayed <elsayed@basecom.de>
 *
 * Posts all provided families to shopware via Rest API
 *
 * Class ShopwareFamilyWriter
 * @package Basecom\Bundle\ShopwareConnectorBundle\Writer
 */
class ShopwareFamilyWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $url;

    /** @var EntityManager $entityManager */
    protected $entityManager;

    /** @var LocaleRepositoryInterface $localeRepository */
    protected $localeRepository;

    /** @var string */
    protected $locale;

    /**
     * ShopwareFamilyWriter constructor.
     *
     * @param EntityManager             $entityManager
     * @param LocaleRepositoryInterface $localeManager
     */
    public function __construct(EntityManager $entityManager, LocaleRepositoryInterface $localeManager)
    {
        $this->entityManager = $entityManager;
        $this->localeRepository = $localeManager;
    }

    /**
     * {@inheritdoc}
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

        $shop = $jobParameters->get('shop');

        /** @var Family $item */
        foreach ($items as $item) {
            $item->setLocale($locale);

            if (null !== $item->getSwId()) {
                $this->createTranslation($item, $apiClient, $shop);
                $this->stepExecution->incrementSummaryInfo('update');
            } else {
                $set = [
                    'name'       => $item->getLabel(),
                    'position'   => 0,
                    'comparable' => true,
                    'sortMode'   => 0
                ];

                $family = $apiClient->post('propertyGroups/', $set);
                $item->setSwId($family['data']['id']);
                $this->entityManager->persist($item);
                $this->createTranslation($item, $apiClient, $shop);
                $this->stepExecution->incrementSummaryInfo('write');

            }
        }
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param $item Family
     * @param $apiClient ApiClient
     * @param $shop int
     *
     * @return mixed
     *
     */
    protected function createTranslation($item, $apiClient, $shop)
    {
        $dataArray = [
            'key' => $item->getSwId(),
            'type' => 'propertygroup',
            'shopId' => $shop,
            'data' => [
                'groupName' => $item->getLabel()
            ]
        ];

        return $apiClient->post('translations/' . $item->getSwId(), $dataArray);
    }
}
