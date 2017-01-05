<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Serializer;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Basecom\Bundle\ShopwareConnectorBundle\Api\Media\CommunityMediaWriter;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Category;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Family;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\FileInfo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;

/**
 * Class ShopwareProductSerializer
 * @package Basecom\Bundle\ShopwareConnectorBundle\Serializer
 */
class ShopwareProductSerializer
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;


    /** @var EntityManagerInterface */
    protected $entityManager;

    /**
     * @var CommunityMediaWriter
     */
    protected $mediaWriter;

    /**
     * @var array
     */
    protected $attributeMapping;

    /**
     * ShopwareProductSerializer constructor.
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param FamilyRepositoryInterface $familyRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param EntityManagerInterface $entityManager
     * @param CommunityMediaWriter $mediaWriter
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        CategoryRepositoryInterface $categoryRepository,
        EntityManagerInterface $entityManager,
        $mediaWriter,
        $attributeMapping
    )
    {
        $this->attributeRepository = $attributeRepository;
        $this->familyRepository = $familyRepository;
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
        $this->mediaWriter = $mediaWriter;
        $this->attributeMapping = $attributeMapping;
    }

    /**
     * @param ProductInterface $product
     * @param                  $attributeMapping
     * @param                  $locale
     * @param                  $filterAttributes
     * @param ApiClient $apiClient
     * @param                  $currency
     *
     * @return array
     */
    public function serialize(
        ProductInterface $product,
        array $attributeMapping,
        $locale,
        $filterAttributes,
        ApiClient $apiClient,
        $currency,
        $jobParameters
    )
    {
        $item = $this->serializeValues($product->getValues(), $product->getAttributes(), $attributeMapping, $locale,
            $apiClient, $filterAttributes, $currency);

        $item['mainDetail']['active'] = $item['active'] = $product->isEnabled();
        $item['categories'] = $this->serializeCategories($product->getCategories(), $locale, $jobParameters->get('rootCategory'));

        if ($product->getFamily() != null) {
            $propertyGroup = $this->serializeFamily($product->getFamily()->getId(), $locale);
            $item['filterGroupId'] = $propertyGroup['id'];
        }

        $item = $this->createVariantGroups($product, $item, $attributeMapping, $currency);
        $item['hasSwId'] = null !== $product->getSwProductId();

        return $item;
    }

    /**
     * @param $productCategories
     * @param $locale
     *
     * @return array
     */
    protected function serializeCategories($productCategories, $locale, $rootCategoryCode)
    {
        $categories = [];
        foreach ($productCategories as $category) {
            /** @var Category $category */
            if ($this->categoryRepository->find($category->getRoot())->getCode() == $rootCategoryCode) {
                $category->setLocale($locale);
                $categories[$category->getSwId()] = [
                    'id' => $category->getSwId(),
                    'name' => $category->getLabel(),
                ];
            }
        }

        return $categories;
    }

    /**
     * @param $attributes
     *
     * @return array
     */
    public function serializeAttributes($attributes)
    {
        $attributeArray = [];
        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            $attribute = $this->attributeRepository->find($attribute->getId());
            array_push($attributeArray, $attribute->getCode());
        }

        return $attributeArray;
    }

    /**
     * @param $familyId
     * @param $locale
     *
     * @return array
     */
    public function serializeFamily($familyId, $locale)
    {
        /** @var Family $family */
        $family = $this->familyRepository->find($familyId);
        $family->setLocale($locale);
        $propertyGroup = [
            'id' => (int)$family->getSwId(),
            'name' => $family->getLabel(),
        ];

        return $propertyGroup;
    }

    /**
     * @param $filterAttributes
     *
     * @return array
     */
    protected function serializeFilterAttributes($filterAttributes)
    {
        $filterAttributes = str_replace(' ', '', $filterAttributes);
        $filterAttributesArray = explode(',', $filterAttributes);

        return $filterAttributesArray;
    }

    /**
     * @param ArrayCollection $values
     * @param array $attributes
     * @param array $attributeMapping
     * @param string $locale
     * @param ApiClient $apiClient
     * @param string $filterAttributes
     * @param string $currency
     *
     * @return array
     */
    public function serializeValues(
        $values,
        $attributes,
        $attributeMapping,
        $locale,
        ApiClient $apiClient,
        $filterAttributes,
        $currency
    )
    {
        $item = [];
        $attributes = $this->serializeAttributes($attributes);

        /** @var ProductValueInterface $value */
        foreach ($values as $value) {
            if (in_array($value->getAttribute()->getCode(), $attributes)) {
                /** @var Attribute $attribute */
                $attribute = $this->attributeRepository->find($value->getAttribute()->getId());
                $attribute->setLocale($locale);

                if (
                    "pim_catalog_image" === $attribute->getAttributeType() ||
                    "pim_assets_collection" === $attribute->getAttributeType()
                ) {
                    $item['__options_images']['replace'] = true;

                    /** @var FileInfo $media */
                    $item = $this->mediaWriter->sendMedia($value, $apiClient, $item);
                }


                if (in_array($attribute->getCode(), $this->serializeFilterAttributes($filterAttributes))) {
                    /** @var Attribute $attribute */
                    $attribute = $this->attributeRepository->find($value->getAttribute()->getId());
                    $attribute->setLocale($locale);
                    $propValue = [];
                    if ($attribute->getBackendType() == 'options') {
                        /** @var AttributeOptionInterface $option */
                        foreach ($value->getOptions() as $option) {
                            $option->setLocale($locale);
                            $propValue['option']['name'] = $attribute->getLabel();
                            $propValue['option']['filterable'] = true;
                            $propValue['value'] = $option->getOptionValue()->getValue();
                            $propValue['position'] = $option->getSortOrder();
                            $item['propertyValues'][] = $propValue;
                        }
                    } else {
                        $propValue['option']['name'] = $attribute->getLabel();
                        $propValue['option']['filterable'] = true;
                        $propValue['value'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                        $item['propertyValues'][] = $propValue;
                    }
                }

                if ($shopwareAttribute = array_search($attribute->getCode(), $attributeMapping)) {
                    $item = $this->setAttributeValue($item, $shopwareAttribute, $attribute, $value, $locale,
                        $currency);
                }
            }
        }

        $this->entityManager->flush();

        return $item;
    }

    /**
     * @param Attribute $attribute
     * @param ProductValueInterface $value
     * @param string $locale
     * @param string $currency
     *
     * @return array|\Datetime|float|null|string
     */
    protected function getAttributeValue(Attribute $attribute, ProductValueInterface $value, $locale, $currency)
    {
        switch ($attribute->getBackendType()) {
            case 'options':
                $options = "";
                $optionsCount = 0;
                foreach ($value->getOptions() as $option) {
                    $option->setLocale($locale);
                    if ($optionsCount > 0) {
                        $options .= ", ";
                    }
                    $options .= $option->getOptionValue()->getValue();
                    $optionsCount++;
                }

                return $options;
                break;
            case 'option':
                $option = $value->getOption();
                $option->setLocale($locale);

                return $option->getOptionValue()->getValue();
                break;
            case 'varchar':
                return $value->getVarchar();
                break;
            case 'text':
                return $value->getText();
                break;
            case 'metric':
                return $value->getMetric();
                break;
            case 'boolean':
                return $value->getBoolean();
                break;
            case 'decimal':
                return $value->getDecimal();
                break;
            case 'date':
                return $value->getDatetime();
                break;
            case 'prices':
                if ($price = $value->getPrice($currency)) {
                    return [$price->getData()];
                } else {
                    return [];
                }
                break;
            default:
                break;
        }

        return null;
    }

    /**
     * @param $product ProductInterface
     * @param $item
     * @param $attributeMapping
     * @param $currency
     * @return mixed
     */
    private function createVariantGroups($product, $item, $attributeMapping, $currency)
    {
        /** @var GroupInterface $variantGroup */
        $variantGroup = $product->getVariantGroup();
        $sku = $product->getIdentifier();

        if ($variantGroup) {
            $item['configuratorSet'] = ['groups' => []];
            $item['configuratorSet']['taxId'] = (string)$product->getValue($attributeMapping['tax']);
            $axisAttributes = $variantGroup->getAxisAttributes();
            /** @var AttributeInterface $axis */
            foreach ($axisAttributes as $key => $axis) {
                $item['configuratorSet']['groups'][$key] = [
                    'name' => $axis->getCode()
                ];

                foreach ($axis->getOptions() as $optionKey => $option) {
                    $item['configuratorSet']['groups'][$key]['options'][$optionKey] = [
                        'name' => (string)$option
                    ];
                }
            }

            $products = $variantGroup->getProducts();
            $item['variants'] = [];
            foreach ($products as $key => $product) {
                if ($isMain = $sku != $product->getIdentifier()) {
                    $product->setIsVariant(true);
                    $this->entityManager->persist($product);
                }

                $item['variants'][$key] = [
                    'isMain' => !$isMain,
                    'number' => (string)$product->getValue($attributeMapping['articleNumber']),
                    'inStock' => isset($attributeMapping['stock']) ? $attributeMapping['stock'] : 0,
                    'additionalText' => (string)$product->getValue($attributeMapping['name']),
                    'prices' => [['price' => $product->getValue($attributeMapping['price']) ? $product->getValue($attributeMapping['price'])->getPrice($currency)->getData() : 0, 'customerGroupKey' => 'EK']]
                ];

                foreach ($item['configuratorSet']['groups'] as $groupKey => $group) {
                    $item['variants'][$key]['configuratorOptions'][$groupKey] = [
                        'group' => $group['name'],
                        'option' => (string)$product->getValue($group['name'])
                    ];
                }
            }

            $this->entityManager->flush();
        }

        return $item;
    }

    /**
     * @param $item
     * @param $shopwareAttribute
     * @param $attribute
     * @param $value
     * @param $locale
     * @param $currency
     * @return array
     */
    protected function setAttributeValue($item, $shopwareAttribute, $attribute, $value, $locale, $currency)
    {
        if($shopwareAttribute == 'price') {
            $item['mainDetail']['prices'][] = ['price' => $this->getAttributeValue($attribute, $value, $locale,
                $currency), 'customerGroupKey' => 'EK'];
        } else {
            if(isset($this->attributeMapping[$shopwareAttribute])) {
                array_walk_recursive($this->attributeMapping[$shopwareAttribute], 'self::walkAttributeMapping', [
                    'attribute' => $attribute,
                    'value' => $value,
                    'locale' => $locale,
                    'currency' => $currency
                ]);
                $item = array_merge($item, $this->attributeMapping[$shopwareAttribute]);
            } else {
                if (strpos($shopwareAttribute, 'attr') !== false) {
                    if ($this->getAttributeValue($attribute, $value, $locale, $currency) != ""
                        && $this->getAttributeValue($attribute, $value, $locale, $currency) != null
                    ) {
                        $item['mainDetail']['attribute'][$shopwareAttribute] = $this->getAttributeValue($attribute,
                            $value, $locale, $currency);
                    }
                }
            }
        }

        return $item;
    }

    /**
     * @param $item
     * @param $key
     * @param $data
     */
    private function walkAttributeMapping(&$item, $key, $data) {
        if(!is_array($item)) {
            $item = $this->getAttributeValue($data['attribute'], $data['value'], $data['locale'], $data['currency']);
        }
    }
}
