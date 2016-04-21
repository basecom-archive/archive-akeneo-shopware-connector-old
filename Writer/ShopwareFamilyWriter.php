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

    /** @var LocaleRepositoryInterface $localeManager */
    protected $localeManager;

    protected $locale;

    /**
     * ShopwareFamilyWriter constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, LocaleRepositoryInterface $localeManager)
    {
        $this->entityManager = $entityManager;
        $this->localeManager = $localeManager;
    }

    public function write(array $items)
    {
        $apiClient = new ApiClient($this->url, $this->userName, $this->apiKey);
        /** @var Family $item */
        foreach($items as $item) {
            $item->setLocale($this->localeManager->getActivatedLocaleCodes()[$this->locale]);
            $set = array(
                'name'      => $item->getLabel(),
                'position'  => 0,
                'comparable'=> true,
                'sortMode'  => 0
            );
            if($item->getSid() != null) {
                if(null == $apiClient->put('propertyGroups/'.$item->getSid(), $set)) {
                    $family = $apiClient->post('propertyGroups/', $set);
                    $item->setSid($family['data']['id']);
                    $this->entityManager->persist($item);
                }
            } else {
                $family = $apiClient->post('propertyGroups/', $set);
                $item->setSid($family['data']['id']);
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

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function getConfigurationFields()
    {
        return [
            'apiKey' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.apiKey.label',
                    'help'  => 'basecom_shopware_connector.export.apiKey.help'
                ]
            ],
            'userName' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.userName.label',
                    'help'  => 'basecom_shopware_connector.export.userName.help'
                ]
            ],
            'url' => [
                'options' => [
                    'label' => 'basecom_shopware_connector.export.url.label',
                    'help'  => 'basecom_shopware_connector.export.url.help'
                ]
            ],
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
        ];
    }
}