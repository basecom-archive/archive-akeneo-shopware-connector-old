<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Writer;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Family;
use Doctrine\ORM\EntityManager;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * Posts all provided families to shopware via Rest API.
 *
 * Class ShopwareFamilyWriter
 */
class ShopwareFamilyWriter extends AbstractConfigurableStepElement implements ItemWriterInterface, StepExecutionAwareInterface
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
        $apiClient = new ApiClient($this->url, $this->userName, $this->apiKey);
        /** @var Family $item */
        foreach ($items as $item) {
            $item->setLocale($this->localeRepository->getActivatedLocaleCodes()[$this->locale]);
            $set = [
                'name'       => $item->getLabel(),
                'position'   => 0,
                'comparable' => true,
                'sortMode'   => 0
            ];
            if (null !== $item->getSwId()) {
                if (null == $apiClient->put('propertyGroups/' . $item->getSwId(), $set)) {
                    $family = $apiClient->post('propertyGroups/', $set);
                    $item->setSwId($family['data']['id']);
                    $this->entityManager->persist($item);
                }
            } else {
                $family = $apiClient->post('propertyGroups/', $set);
                $item->setSwId($family['data']['id']);
                $this->entityManager->persist($item);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'apiKey'   => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.apiKey.label',
                    'help'  => 'basecom_shopware_connector.export.apiKey.help',
                ],
            ],
            'userName' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.userName.label',
                    'help'  => 'basecom_shopware_connector.export.userName.help',
                ],
            ],
            'url'      => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.url.label',
                    'help'  => 'basecom_shopware_connector.export.url.help',
                ],
            ],
            'locale'   => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->localeRepository->getActivatedLocaleCodes(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'basecom_shopware_connector.export.locale.label',
                    'help'     => 'basecom_shopware_connector.export.locale.help'
                ]
            ],
        ];
    }
}
