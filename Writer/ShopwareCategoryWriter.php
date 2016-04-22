<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Writer;

use Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository\CategoryRepository;
use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
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
class ShopwareCategoryWriter extends AbstractConfigurableStepElement implements ItemWriterInterface, StepExecutionAwareInterface
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
    protected $localeManager;

    /**
     * ShopwareCategoryWriter constructor.
     * @param CategoryRepository $categoryRepository
     * @param EntityManager $entityManager
     * @param LocaleRepositoryInterface $localeManager
     */
    public function __construct(CategoryRepository $categoryRepository, EntityManager $entityManager, LocaleRepositoryInterface $localeManager)
    {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
        $this->localeManager = $localeManager;
    }

    /**
     * posts categories to Shopware
     *
     * @param array $items
     */
    public function write(array $items)
    {
        $apiClient = new ApiClient($this->url, $this->userName, $this->apiKey);

        /** @var Category $item */
        foreach($items as $item) {
            $item->setLocale($this->localeManager->getActivatedLocaleCodes()[$this->locale]);
            $parent = 1;
            if($item->getParent() != null && $item->getParent()->getSwId() != null) {
                $parent = $item->getParent()->getSwId();
            }
            $swCategory = array(
                'name'              => $item->getLabel(),
                'parentId'          => $parent,
                'active'            => true,
                'blog'              => false,
                'showFilterGroups'  => true,
            );
            if($item->getSwId() != null) {
                if(null == $apiClient->put('categories/'.$item->getSwId(), $swCategory)) {
                    $category = $apiClient->post('categories', $swCategory);
                    $item->setSwId($category['data']['id']);
                    $this->entityManager->persist($item);
                }
            } else {
                $category = $apiClient->post('categories', $swCategory);
                $item->setSwId($category['data']['id']);
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
     * @return LocaleRepositoryInterface
     */
    public function getLocaleManager()
    {
        return $this->localeManager;
    }

    /**
     * @param LocaleRepositoryInterface $localeManager
     */
    public function setLocaleManager($localeManager)
    {
        $this->localeManager = $localeManager;
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
            'locale' => [
                'type' => 'choice',
                'options' => [
                    'choices'   => $this->localeManager->getActivatedLocaleCodes(),
                    'required'  => true,
                    'select2'   => true,
                    'label'     => 'basecom_shopware_connector.export.locale.label',
                    'help'      => 'basecom_shopware_connector.export.locale.help'
                ]
            ],
            'url' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.url.label',
                    'help'  => 'basecom_shopware_connector.export.url.help'
                ]
            ],
            'userName' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.userName.label',
                    'help'  => 'basecom_shopware_connector.export.userName.help'
                ]
            ],
            'apiKey' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.apiKey.label',
                    'help'  => 'basecom_shopware_connector.export.apiKey.help'
                ]
            ]
        ];
    }
}
