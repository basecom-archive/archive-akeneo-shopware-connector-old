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
     */
    public function __construct(CategoryRepository $categoryRepository, EntityManager $entityManager, LocaleRepositoryInterface $localeManager)
    {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
        $this->localeManager = $localeManager;
    }

    public function write(array $items)
    {
        $apiClient = new ApiClient($this->url, $this->userName, $this->apiKey);

        /** @var Category $item */
        foreach($items as $item) {
            $item->setLocale($this->localeManager->getActivatedLocaleCodes()[$this->locale]);
            $parent = 1;
            if($item->getParent() != null && $item->getParent()->getSid() != null) {
                $parent = $item->getParent()->getSid();
            }
            $swCategory = array(
                'name'              => $item->getLabel(),
                'parentId'          => $parent,
                'active'            => true,
                'blog'              => false,
                'showFilterGroups'  => true,
            );
            if($item->getSid() != null) {
                if(null == $apiClient->put('categories/'.$item->getSid(), $swCategory)) {
                    $category = $apiClient->post('categories', $swCategory);
                    $item->setSid($category['data']['id']);
                    $this->entityManager->persist($item);
                }
            } else {
                $category = $apiClient->post('categories', $swCategory);
                $item->setSid($category['data']['id']);
                $this->entityManager->persist($item);
            }
            echo $item->getLabel()."\n";
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

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function getConfigurationFields()
    {
        return [
            'locale' => [
                'type' => 'choice',
                'options' => [
                    'choices'   => $this->localeManager->getActivatedLocaleCodes(),
                    'required'  => true,
                    'select2'   => true,
                    'label'     => 'Locale',
                    'help'      => 'locale'
                ]
            ],
            'url' => [
                'options' => [
                    'label' => 'URL',
                    'help'  => 'URL of the Shopware Shop'
                ]
            ],
            'userName' => [
                'options' => [
                    'label' => 'Username',
                    'help'  => 'Username of the Shopware API-User'
                ]
            ],
            'apiKey' => [
                'options' => [
                    'label' => 'API-Key',
                    'help'  => 'Shopware API-Key'
                ]
            ]
        ];
    }
}