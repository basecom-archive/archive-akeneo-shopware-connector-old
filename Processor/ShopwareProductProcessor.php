<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Processor;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Basecom\Bundle\ShopwareConnectorBundle\Serializer\ShopwareProductSerializer;

/**
 * processes the product for the export to shopware
 *
 * Class ShopwareProductProcessor
 * @package Basecom\Bundle\ShopwareConnectorBundle\Processor
 */
class ShopwareProductProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var ShopwareProductSerializer */
    protected $serializer;

    /**
     * ShopwareProductProcessor constructor.
     *
     * @param ShopwareProductSerializer $serializer
     */
    public function __construct(ShopwareProductSerializer $serializer) {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        if($item->isVariant()) {
            return null;
        }

        $jobParameters = $this->stepExecution->getJobParameters();
        $locale = $jobParameters->get('locale');

        $apiClient = new ApiClient(
            $jobParameters->get('url'),
            $jobParameters->get('userName'),
            $jobParameters->get('apiKey')
        );

        $attributeMapping = $this->convertConfigurationVariablesToMappingArray();

        return $this->serializer->serialize($item, $attributeMapping,
            $locale, $jobParameters->get('filterAttributes'), $apiClient,
            $jobParameters->get('currency'), $jobParameters);
    }

    /**
     * maps the configuration fields variables to an array
     *
     * @return array
     */
    protected function convertConfigurationVariablesToMappingArray()
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        $configArray = [
            'articleNumber'    => $jobParameters->get('articleNumber'),
            'name'             => $jobParameters->get('name'),
            'description'      => $jobParameters->get('description'),
            'descriptionLong'  => $jobParameters->get('descriptionLong'),
            'pseudoSales'      => $jobParameters->get('pseudoSales'),
            'highlight'        => $jobParameters->get('highlight'),
            'keywords'         => $jobParameters->get('keywords'),
            'metaTitle'        => $jobParameters->get('metaTitle'),
            'priceGroupActive' => $jobParameters->get('priceGroupActive'),
            'lastStock'        => $jobParameters->get('lastStock'),
            'notification'     => $jobParameters->get('notification'),
            'template'         => $jobParameters->get('template'),
            'supplier'         => $jobParameters->get('supplier'),
            'inStock'          => $jobParameters->get('inStock'),
            'stockMin'         => $jobParameters->get('stockMin'),
            'weight'           => $jobParameters->get('weight'),
            'len'              => $jobParameters->get('len'),
            'height'           => $jobParameters->get('height'),
            'ean'              => $jobParameters->get('ean'),
            'minPurchase'      => $jobParameters->get('minPurchase'),
            'purchaseSteps'    => $jobParameters->get('purchaseSteps'),
            'maxPurchase'      => $jobParameters->get('maxPurchase'),
            'purchaseUnit'     => $jobParameters->get('purchaseUnit'),
            'referenceUnit'    => $jobParameters->get('referenceUnit'),
            'packUnit'         => $jobParameters->get('packUnit'),
            'shippingFree'     => $jobParameters->get('shippingFree'),
            'releaseDate'      => $jobParameters->get('releaseDate'),
            'shippingTime'     => $jobParameters->get('shippingTime'),
            'width'            => $jobParameters->get('width'),
            'price'            => $jobParameters->get('price'),
            'pseudoPrice'      => $jobParameters->get('pseudoPrice'),
            'basePrice'        => $jobParameters->get('basePrice'),
            'tax'              => $jobParameters->get('tax')
        ];
        $attributes = explode(";", $jobParameters->get('attr'));
        foreach ($attributes as $attribute) {
            $attr = explode(":", $attribute);
            if (isset($attr[1])) {
                $configArray[$attr[0]] = $attr[1];
            }
        }

        return $configArray;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

}
