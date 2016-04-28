<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Serializer;

use Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository\CategoryRepository;
use Akeneo\Bundle\FileStorageBundle\Doctrine\ORM\Repository\FileInfoRepository;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Category;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Family;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\FileInfo;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\FamilyRepository;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\Model\Association;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Class ShopwareProductSerializer
 * @package Basecom\Bundle\ShopwareConnectorBundle\Serializer
 */
class ShopwareProductSerializer
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var FamilyRepository */
    protected $familyRepository;

    /** @var CategoryRepository */
    protected $categoryRepository;

    /** @var FileInfoRepository */
    protected $fileInfoRepository;

    /** @var EntityManager */
    protected $entityManager;

    /** @var string */
    protected $rootDir;

    /**
     * ShopwareProductSerializer constructor.
     * @param AttributeRepository $attributeRepository
     * @param FamilyRepository $familyRepository
     * @param CategoryRepository $categoryRepository
     * @param $rootDir
     * @param FileInfoRepository $fileInfoRepository
     * @param EntityManager $entityManager
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        FamilyRepository $familyRepository,
        CategoryRepository $categoryRepository,
        $rootDir,
        FileInfoRepository $fileInfoRepository,
        EntityManager $entityManager
    )
    {
        $this->attributeRepository  = $attributeRepository;
        $this->familyRepository     = $familyRepository;
        $this->categoryRepository   = $categoryRepository;
        $this->rootDir              = $rootDir;
        $this->fileInfoRepository   = $fileInfoRepository;
        $this->entityManager        = $entityManager;
    }

    /**
     * @param Product $product
     * @param $attributeMapping
     * @param $locale
     * @param $filterAttributes
     * @param ApiClient $apiClient
     * @param $currency
     * @return array
     */
    public function serialize(Product $product, $attributeMapping, $locale, $filterAttributes, ApiClient $apiClient, $currency)
    {
        $similar = $attributeMapping['similar'];
        $related = $attributeMapping['related'];
        unset($attributeMapping['similar']);
        unset($attributeMapping['related']);
        $item = $this->serializeValues($product->getValues(), $product->getAttributes(), $attributeMapping, $locale, $apiClient, $filterAttributes, $currency);
        $item['mainDetail']['active']   = $item['active'] = $product->isEnabled();
        $item['categories']             = $this->serializeCategories($product->getCategories(), $locale);
        $associations                   = $this->serializeAssociations($product, $similar, $related);
        $item['similar']                = $associations['similar'];
        $item['related']                = $associations['related'];

        if($product->getFamily() != null) {
            $propertyGroup = $this->serializeFamily($product->getFamily()->getId(), $locale);
            $item['filterGroupId']          = $propertyGroup['id'];
        }

        return $item;
    }

    /**
     * @param $productCategories
     * @param $locale
     * @return array
     */
    protected function serializeCategories($productCategories, $locale)
    {
        $categories = array();
        foreach($productCategories as $category) {
            /** @var Category $category */
            $category = $this->categoryRepository->find($category->getId());
            $category->setLocale($locale);
            $categories[$category->getSwId()] = array(
                'id'    => $category->getSwId(),
                'name'  => $category->getLabel(),
            );
        }
        return $categories;
    }

    /**
     * @param $attributes
     * @return array
     */
    public function serializeAttributes($attributes)
    {
        $attributeArray = array();
        /** @var Attribute $attribute */
        foreach($attributes as $attribute) {
            $attribute = $this->attributeRepository->find($attribute->getId());
            array_push($attributeArray, $attribute->getCode());
        }
        return $attributeArray;
    }

    /**
     * @param $familyId
     * @param $locale
     * @return array
     */
    public function serializeFamily($familyId, $locale)
    {
        /** @var Family $family */
        $family = $this->familyRepository->find($familyId);
        $family->setLocale($locale);
        $propertyGroup = array(
            'id'  => (int)$family->getSwId(),
            'name' => $family->getLabel(),
        );
        return $propertyGroup;
    }

    /**
     * @param $filterAttributes
     * @return array
     */
    protected function serializeFilterAttributes($filterAttributes)
    {
        $filterAttributes = str_replace(' ', '', $filterAttributes);
        $filterAttributesArray = explode(',', $filterAttributes);
        return $filterAttributesArray;
    }

    /**
     * @param $values
     * @param $attributes
     * @param $attributeMapping
     * @param $locale
     * @param ApiClient $apiClient
     * @param $filterAttributes
     * @param $currency
     * @return array
     */
    public function serializeValues($values, $attributes, $attributeMapping, $locale, ApiClient $apiClient, $filterAttributes, $currency)
    {
        $item = array();
        $imageCount = 0;
        $propValueCount = 0;
        $attributes = $this->serializeAttributes($attributes);

        /** @var ProductValueInterface $value*/
        foreach($values as $value) {
            if(in_array($value->getAttribute()->getCode(), $attributes)) {
                /** @var Attribute $attribute */
                $attribute = $this->attributeRepository->find($value->getAttribute()->getId());
                $attribute->setLocale($locale);

                if($attribute->getAttributeType() == "pim_catalog_image") {
                    /** @var FileInfo $media */
                    $fileInfo = $this->fileInfoRepository->find($value->getMedia());
                    if($fileInfo->getSwMediaId() == null) {
                        $path = $this->rootDir."/file_storage/catalog/".$value->getMedia()->getKey();
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_get_contents($path);
                        $base64 = 'data:image/'.$type.';base64,'.base64_encode($data);
                        $mediaArray = array(
                            'album'       => -1,
                            'file'        => $base64,
                            'description' => $value->getMedia()->getOriginalFilename(),
                        );
                        $media = $apiClient->post('media', $mediaArray);
                        $mediaId = $media['data']['id'];
                        $item['images'][$imageCount] = array('mediaId' => $mediaId);
                        $fileInfo->setSwMediaId($mediaId);
                        $this->entityManager->persist($fileInfo);
                        $imageCount++;
                    }
                }

                if(in_array($attribute->getCode(), $this->serializeFilterAttributes($filterAttributes))) {
                    /** @var Attribute $attribute */
                    $attribute = $this->attributeRepository->find($value->getAttribute()->getId());
                    $attribute->setLocale($locale);
                    $propValue = array();
                    if($attribute->getBackendType() == 'options') {
                        /** @var AttributeOptionInterface $option */
                        foreach($value->getOptions() as $option) {
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

                if($shopwareAttribute = array_search($attribute->getCode(), $attributeMapping)) {
                    switch($shopwareAttribute) {
                        case 'articleNumber':
                            $item['mainDetail']['number'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
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
                            $item['priceGroupActive'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
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
                            $item['mainDetail']['inStock'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'stockMin':
                            $item['mainDetail']['stockMin'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'weight':
                            $item['mainDetail']['weight'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'len':
                            $item['mainDetail']['len'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'height':
                            $item['mainDetail']['height'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'ean':
                            $item['mainDetail']['ean'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'minPurchase':
                            $item['mainDetail']['minPurchase'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'purchaseSteps':
                            $item['mainDetail']['purchaseSteps'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'maxPurchase':
                            $item['mainDetail']['maxPurchase'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'purchaseUnit':
                            $item['mainDetail']['purchaseUnit'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'referenceUnit':
                            $item['mainDetail']['referenceUnit'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'packUnit':
                            $item['mainDetail']['packUnit'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'shippingFree':
                            $item['mainDetail']['shippingFree'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'releaseDate':
                            $item['mainDetail']['releaseDate'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'shippingTime':
                            $item['mainDetail']['shippingTime'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'width':
                            $item['mainDetail']['width'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'price':
                            $item['mainDetail']['prices'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        case 'tax':
                            $item['tax'] = $this->getAttributeValue($attribute, $value, $locale, $currency);
                            break;
                        default:
                            if(strpos($shopwareAttribute, 'attr') !== false) {
                                if($this->getAttributeValue($attribute, $value, $locale, $currency) != "" && $this->getAttributeValue($attribute, $value, $locale, $currency) != null) {
                                    $item['mainDetail']['attribute'][$shopwareAttribute] = $this->getAttributeValue($attribute, $value, $locale, $currency);
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
     * @param Attribute $attribute
     * @param ProductValueInterface $value
     * @param $locale
     * @param $currency
     * @return array|\Datetime|float|null|string
     */
    protected function getAttributeValue(Attribute $attribute, ProductValueInterface $value, $locale, $currency) {
        switch($attribute->getBackendType()) {
            case 'options':
                $options = "";
                $optionsCount = 0;
                foreach($value->getOptions() as $option) {
                    $option->setLocale($locale);
                    if($optionsCount > 0) $options .= ", ";
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
                return array(
                    array(
                        'price' => $value->getPrice($currency)->getData(),
                    ));
                break;
            default:
                break;
        }
        return null;
    }

    /**
     * @param Product $product
     * @param string  $similar
     * @param string  $related
     *
     * @return array
     */
    public function serializeAssociations($product, $similar, $related) {
        $related = $this->serializeRelated($product->getAssociationForTypeCode($related));
        $similar = $this->serializeSimilar($product->getAssociationForTypeCode($similar));
        $associations = array(
            'related' => $related,
            'similar' => $similar,
        );
        return $associations;
    }

    /**
     * @param Association $association
     * @return array
     */
    protected function serializeSimilar($association) {
        $similar = [];
        if($association === null) return $similar;
        foreach($association->getProducts() as $associationProduct) {
            array_push($similar, array(
                'number'    => (string)$associationProduct->getIdentifier(),
            ));
        }
        return $similar;
    }

    /**
     * @param Association $association
     * @return array
     */
    protected function serializeRelated($association) {
        $related = [];
        if($association === null) return $related;
        foreach($association->getProducts() as $associationProduct) {
            echo (string)$associationProduct->getIdentifier()."\n\n";
            array_push($related, array(
                'number'    => (string)$associationProduct->getIdentifier(),
            ));
        }
        return $related;
    }
}
