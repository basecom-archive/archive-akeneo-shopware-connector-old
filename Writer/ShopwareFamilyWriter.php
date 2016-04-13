<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Family;
use Doctrine\ORM\EntityManager;
use Gaufrette\Adapter\Local;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;

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

    /** @var LocaleManager $localeManager */
    protected $localeManager;

    protected $locale;

    /**
     * ShopwareFamilyWriter constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, LocaleManager $localeManager)
    {
        $this->entityManager = $entityManager;
        $this->localeManager = $localeManager;
    }

    public function write(array $items)
    {
        echo "Family export writer...";
        $apiClient = new ApiClient($this->url, $this->userName, $this->apiKey);
        /** @var Family $item */
        foreach($items as $item) {
            $item->setLocale($this->localeManager->getActiveCodes()[$this->locale]);
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
                    'label' => 'API-Key',
                    'help'  => 'pim_connector.import.filePath.help'
                ]
            ],
            'userName' => [
                'options' => [
                    'label' => 'Username',
                    'help'  => 'HELP!'
                ]
            ],
            'url' => [
                'options' => [
                    'label' => 'URL',
                    'help'  => 'help'
                ]
            ],
            'locale' => [
                'type' => 'choice',
                'options' => [
                    'choices'   => $this->localeManager->getActiveCodes(),
                    'required'  => true,
                    'select2'   => true,
                    'label'     => 'Locale',
                    'help'      => 'help'
                ]
            ],
        ];
    }
}