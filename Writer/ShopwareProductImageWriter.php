<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Writer;


use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use MongoDBODMProxies\__CG__\Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository;

class ShopwareProductImageWriter extends AbstractConfigurableStepElement implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $url;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var ApiClient */
    protected $apiClient;

    protected $password;

    /**
     * ShopwareProductImageWriter constructor.
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(AttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function write(array $items)
    {
        $start = time();
        echo "ProductImageWriter...";
        $this->apiClient = new ApiClient($this->url, $this->userName, $this->apiKey);

        foreach($items as $item) {
            if(isset($item['image_path'])) {
                foreach($item['image_path'] as $imagePath) {
                    $path = $imagePath;
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $base64 = 'data:image/'.$type.';base64,'.base64_encode($data);

                    $mediaArray = array(
                        'album'       => -1,
                        'file'        => $base64,
                        'description' => "desc",
                    );
                    $media = $this->apiClient->post('media', $mediaArray);
                    var_dump($media);
                }
            }
        }

        $end = time();
        $runtime = $end-$start;
        echo "\nLaufzeit: ".$runtime." Sekunden!\n";
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
                    'help'  => 'Enter the API-Key'
                ]
            ],
            'userName' => [
                'options' => [
                    'label' => 'Username',
                    'help'  => 'Enter the API-Username'
                ]
            ],
            'url' => [
                'options' => [
                    'label' => 'URL',
                    'help'  => 'Enter the API-URL'
                ]
            ]
        ];
    }



    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
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
}