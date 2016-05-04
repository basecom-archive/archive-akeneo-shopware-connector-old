<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Serializer;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Category;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Family;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\FileInfo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

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

    /** @var FileInfoRepositoryInterface */
    protected $fileInfoRepository;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var string */
    protected $rootDir;

    /**
     * ShopwareProductSerializer constructor.
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param FamilyRepositoryInterface    $familyRepository
     * @param CategoryRepositoryInterface  $categoryRepository
     * @param FileInfoRepositoryInterface  $fileInfoRepository
     * @param EntityManagerInterface       $entityManager
     * @param string                       $rootDir
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        CategoryRepositoryInterface $categoryRepository,
        FileInfoRepositoryInterface $fileInfoRepository,
        EntityManagerInterface $entityManager,
        $rootDir
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->familyRepository = $familyRepository;
        $this->categoryRepository = $categoryRepository;
        $this->rootDir = $rootDir;
        $this->fileInfoRepository = $fileInfoRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ProductInterface $product
     * @param                  $attributeMapping
     * @param                  $locale
     * @param                  $filterAttributes
     * @param ApiClient        $apiClient
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
        $currency
    ) {
        $similar = $attributeMapping['similar'];
        $related = $attributeMapping['related'];
        unset($attributeMapping['similar']);
        unset($attributeMapping['related']);
        $item = $this->serializeValues($product->getValues(), $product->getAttributes(), $attributeMapping, $locale,
            $apiClient, $filterAttributes, $currency);
        $item['mainDetail']['active'] = $item['active'] = $product->isEnabled();
        $item['categories'] = $this->serializeCategories($product->getCategories(), $locale);
        $associations = $this->serializeAssociations($product, $similar, $related);
        $item['similar'] = $associations['similar'];
        $item['related'] = $associations['related'];

        if ($product->getFamily() != null) {
            $propertyGroup = $this->serializeFamily($product->getFamily()->getId(), $locale);
            $item['filterGroupId'] = $propertyGroup['id'];
        }

        return $item;
    }

    /**
     * @param $productCategories
     * @param $locale
     *
     * @return array
     */
    protected function serializeCategories($productCategories, $locale)
    {
        $categories = [];
        foreach ($productCategories as $category) {
            /** @var Category $category */
            $category = $this->categoryRepository->find($category->getId());
            $category->setLocale($locale);
            $categories[$category->getSwId()] = [
                'id'   => $category->getSwId(),
                'name' => $category->getLabel(),
            ];
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
            'id'   => (int)$family->getSwId(),
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
     * @param array           $attributes
     * @param array           $attributeMapping
     * @param string          $locale
     * @param ApiClient       $apiClient
     * @param string          $filterAttributes
     * @param string          $currency
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
    ) {
        $item = [];
        $imageCount = 0;
        $propValueCount = 0;
        $attributes = $this->serializeAttributes($attributes);

        /** @var ProductValueInterface $value */
        foreach ($values as $value) {
            if (in_array($value->getAttribute()->getCode(), $attributes)) {
                /** @var Attribute $attribute */
                $attribute = $this->attributeRepository->find($value->getAttribute()->getId());
                $attribute->setLocale($locale);

                if ($attribute->getAttributeType() == "pim_catalog_image") {
                    /** @var FileInfo $media */
                    $fileInfo = $this->fileInfoRepository->find($value->getMedia());
                    if ($fileInfo->getSwMediaId() == null) {
                        $path = $this->rootDir . "/file_storage/catalog/" . $value->getMedia()->getKey();
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_get_contents($path);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        $mediaArray = [
                            'album'       => -1,
                            'file'        => $base64,
                            'description' => $value->getMedia()->getOriginalFilename(),
                        ];
                        $media = $apiClient->post('media', $mediaArray);
                        $mediaId = $media['data']['id'];
                        $item['images'][$imageCount] = ['mediaId' => $mediaId];
                        $fileInfo->setSwMediaId($mediaId);
                        $this->entityManager->persist($fileInfo);
                        $imageCount++;
                    }
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
                            $item['propertyValues'][$propValueCount] = $propValue;
                            $propValueCount++;
                        }
                    } else {
                        $propValue['option']['name'] = $attribute->getLabel();
                        $propValue['option']['filterable'] = true;
                        $propValue['value'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                        $item['propertyValues'][$propValueCount] = $propValue;
                        $propValueCount++;
                    }
                }

                if ($shopwareAttribute = array_search($attribute->getCode(), $attributeMapping)) {
                    switch ($shopwareAttribute) {
                        case 'articleNumber':
                            $item['mainDetail']['number'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'name':
                            $item['name'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'description':
                            $item['description'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'descriptionLong':
                            $item['descriptionLong'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'pseudoSales':
                            $item['pseudoSales'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'highlight':
                            $item['highlight'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'keywords':
                            $item['keywords'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'metaTitle':
                            $item['metaTitle'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'priceGroupActive':
                            $item['priceGroupActive'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'lastStock':
                            $item['lastStock'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'notification':
                            $item['notification'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'template':
                            $item['template'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'supplier':
                            $item['supplier'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'inStock':
                            $item['mainDetail']['inStock'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'stockMin':
                            $item['mainDetail']['stockMin'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'weight':
                            $item['mainDetail']['weight'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'len':
                            $item['mainDetail']['len'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'height':
                            $item['mainDetail']['height'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'ean':
                            $item['mainDetail']['ean'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'minPurchase':
                            $item['mainDetail']['minPurchase'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'purchaseSteps':
                            $item['mainDetail']['purchaseSteps'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'maxPurchase':
                            $item['mainDetail']['maxPurchase'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'purchaseUnit':
                            $item['mainDetail']['purchaseUnit'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'referenceUnit':
                            $item['mainDetail']['referenceUnit'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'packUnit':
                            $item['mainDetail']['packUnit'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'shippingFree':
                            $item['mainDetail']['shippingFree'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'releaseDate':
                            $item['mainDetail']['releaseDate'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'shippingTime':
                            $item['mainDetail']['shippingTime'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'width':
                            $item['mainDetail']['width'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'price':
                            $item['mainDetail']['prices'] = $this->getAttributeValue($attribute, $value, $locale,
                                $currency);
                            break;
                        case 'tax':
                            $item['tax'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        default:
                            if (strpos($shopwareAttribute, 'attr') !== false) {
                                if ($this->getAttributeValue($attribute, $value, $locale,
                                        $currency) != "" && $this->getAttributeValue($attribute, $value, $locale,
                                        $currency) != null
                                ) {
                                    $item['mainDetail']['attribute'][$shopwareAttribute] = $this->getAttributeValue($attribute,
                                        $value, $locale, $currency);
                                }
                            }
                            break;
                    }
                }
            }
        }
        $this->entityManager->flush();

        return $item;
    }

    /**
     * @param Attribute             $attribute
     * @param ProductValueInterface $value
     * @param string                $locale
     * @param string                $currency
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
                return [
                    [
                        'price' => $value->getPrice($currency)->getData(),
                    ]
                ];
                break;
            default:
                break;
        }

        return null;
    }

    /**
     * @param ProductInterface $product
     * @param string           $similar
     * @param string           $related
     *
     * @return array
     */
    public function serializeAssociations(ProductInterface $product, $similar, $related)
    {
        $associations = array(
            'related' => null,
            'similar' => null,
        );
        
        if(null != $product->getAssociationForTypeCode($related)) {
            $related = $this->serializeRelated($product->getAssociationForTypeCode($related));
            $associations['related'] = $related;
        }
        if(null != $product->getAssociationForTypeCode($similar)) {
            $similar = $this->serializeSimilar($product->getAssociationForTypeCode($similar));
            $associations['similar'] = $similar;
        }

        return $associations;
    }

    /**
     * @param AssociationInterface $association
     *
     * @return array
     */
    protected function serializeSimilar(AssociationInterface $association)
    {
        $similar = [];
        if ($association === null) {
            return $similar;
        }
        foreach ($association->getProducts() as $associationProduct) {
            array_push($similar, [
                'number' => (string)$associationProduct->getIdentifier(),
            ]);
        }

        return $similar;
    }

    /**
     * @param AssociationInterface $association
     *
     * @return array
     */
    protected function serializeRelated(AssociationInterface $association)
    {
        $related = [];
        if ($association === null) {
            return $related;
        }
        foreach ($association->getProducts() as $associationProduct) {
            echo (string)$associationProduct->getIdentifier() . "\n\n";
            array_push($related, [
                'number' => (string)$associationProduct->getIdentifier(),
            ]);
        }

        return $related;
    }
}
