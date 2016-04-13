<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Reader;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;

class ShopwareCategoryReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $url;

    protected $jsonData;

    protected $count;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function read()
    {
        if($this->jsonData === null) {
            echo "Category import...\n";
            $client = new ApiClient($this->url, $this->userName, $this->apiKey);
            $this->jsonData = $client->get('categories');
            $this->count = 0;
        }
        if($this->count >= $this->jsonData['total']) return null;

        $category = [];

        $cat = $this->categoryRepository->findOneBy(array('sid' => $this->jsonData['data'][$this->count]['id']));
        if($cat != null && $cat->getSid() != $cat->getCode()) {
            $category['code'] = $cat->getCode();
        } else {
            $category['code'] = $this->jsonData['data'][$this->count]['id'];
        }
        if($this->jsonData['data'][$this->count]['parentId'] === null) {
            $label = "Shopware Catalog";
        } else {
            $label = $this->jsonData['data'][$this->count]['name'];
            $parentId = $this->jsonData['data'][$this->count]['parentId'];
            $category['parent'] = $parentId;
        }
        $category['sid'] = (int) $this->jsonData['data'][$this->count]['id'];
        $category['label-en_US'] = $label;
        $category['label-de_DE'] = $label;
        //$category['sid'] = $this->jsonData['data'][$this->count]['id'];

        $this->count++;
        return $category;
    }

    public function createCategoryCode($label) {
        $code = str_replace("&", "_", strtolower($label)).$this->jsonData['data'][$this->count]['id'];
        $code = str_replace(" ", "", $code);
        $code = str_replace("ö", "oe", $code);
        $code = str_replace("Ö", "oe", $code);
        $code = str_replace("ä", "ae", $code);
        $code = str_replace("Ä", "ae", $code);
        $code = str_replace("ü", "ue", $code);
        $code = str_replace("Ü", "ue", $code);
        $code = str_replace("ß", "ss", $code);

        return $code;
    }

    public function getParentCategoryCode($parentId)
    {
        foreach($this->jsonData['data'] as $data) {
            if($data['id'] == $parentId) {
                $parentLabel = $data['name'];
                return $this->createCategoryCode($parentLabel);
            }
        }
        return null;
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
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getJsonData()
    {
        return $this->jsonData;
    }

    /**
     * @param mixed $jsonData
     */
    public function setJsonData($jsonData)
    {
        $this->jsonData = $jsonData;
    }

    /**
     * @return mixed
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param mixed $count
     */
    public function setCount($count)
    {
        $this->count = $count;
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
            ]
        ];
    }
}