<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Serializer;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Basecom\Bundle\ShopwareConnectorBundle\Api\Media\CommunityMediaWriter;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Category;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Family;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\FileInfo;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;

/**
 * @author  Amir El Sayed <elsayed@basecom.de>
 *
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

    private   $mediaWriter;

    /**
     * ShopwareProductSerializer constructor.
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param FamilyRepositoryInterface    $familyRepository
     * @param CategoryRepositoryInterface  $categoryRepository
     * @param EntityManagerInterface       $entityManager
     * @param CommunityMediaWriter         $mediaWriter
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        CategoryRepositoryInterface $categoryRepository,
        EntityManagerInterface $entityManager,
        $mediaWriter
    )
    {
        $this->attributeRepository = $attributeRepository;
        $this->familyRepository    = $familyRepository;
        $this->categoryRepository  = $categoryRepository;
        $this->entityManager       = $entityManager;
        $this->mediaWriter         = $mediaWriter;
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
        $currency,
        $jobParameters
    )
    {
        $item = $this->serializeValues($product, $product->getAttributes(), $attributeMapping, $locale,
            $apiClient, $filterAttributes, $currency, $jobParameters->get('channel'));

        $item['mainDetail']['active'] = $item['active'] = $product->isEnabled();
        $item['categories']           = $this->serializeCategories($product->getCategories(), $jobParameters->get('rootCategory'));
        list($item['similar'], $item['related']) = $this->serializeAssociations($product, $attributeMapping['similar'], $attributeMapping['related']);

        if ($product->getFamily() != null) {
            $propertyGroup         = $this->serializeFamily($product->getFamily()->getId(), $locale);
            $item['filterGroupId'] = $propertyGroup['id'];
        }

        $item            = $this->createVariantGroups($product, $item, $attributeMapping, $currency, $locale, $jobParameters->get('channel'));
        $item['hasSwId'] = null !== $product->getSwProductId();
        $item['swId']    = $product->getSwProductId();

        return $item;
    }

    /**
     * @param $productCategories Category[]
     * @param $rootCategoryCode
     *
     * @return array
     * @internal param $locale
     *
     */
    protected function serializeCategories($productCategories, $rootCategoryCode)
    {
        $categories = [];
        foreach ($productCategories as $category) {
            if ($this->categoryRepository->find($category->getRoot())->getCode() == $rootCategoryCode) {
                foreach ($category->getSwIds() as $catLocale => $localeSwId) {
                    $category->setLocale($catLocale);
                    $categories[$localeSwId] = [
                        'id'   => $localeSwId,
                        'name' => $category->getLabel(),
                    ];
                }
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

        return array_unique($attributeArray);
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
        $filterAttributes      = str_replace(' ', '', $filterAttributes);
        $filterAttributesArray = explode(',', $filterAttributes);

        return $filterAttributesArray;
    }

    /**
     * @param Product     $product
     * @param Attribute[] $attributes
     * @param array       $attributeMapping
     * @param string      $locale
     * @param ApiClient   $apiClient
     * @param string      $filterAttributes
     * @param string      $currency
     *
     * @return array
     */
    public function serializeValues(
        $product,
        $attributes,
        $attributeMapping,
        $locale,
        ApiClient $apiClient,
        $filterAttributes,
        $currency,
        $scope
    )
    {
        $item                  = [];
        $localizableAttributes = [];
        $item['tax']           = 19;
        /** @var ProductValueInterface $value */
        foreach ($attributes as $attribute) {
            if ($attribute->isLocalizable()) {
                $attribute->setLocale($locale);
            }

            $value = $product->getValue($attribute->getCode(), $locale, $scope);

            if (
                "pim_catalog_image" === $attribute->getAttributeType() ||
                "pim_assets_collection" === $attribute->getAttributeType()
            ) {
                $item['__options_images']['replace'] = true;

                /** @var FileInfo $media */
                $item = $this->mediaWriter->sendMedia($value, $apiClient, $item);
            }

            if (in_array($attribute->getCode(), $this->serializeFilterAttributes($filterAttributes))) {
                $propValue = [];
                if ($attribute->getBackendType() == 'options') {
                    /** @var AttributeOptionInterface $option */
                    foreach ($value->getOptions() as $option) {
                        $option->setLocale($locale);
                        $propValue['option']['name']       = $attribute->getLabel();
                        $propValue['option']['filterable'] = true;
                        $propValue['value']                = $option->getOptionValue()->getValue();
                        $propValue['position']             = $option->getSortOrder();
                        $item['propertyValues'][]          = $propValue;
                    }
                } else {
                    $propValue['option']['name']       = $attribute->getLabel();
                    $propValue['option']['filterable'] = true;
                    $propValue['value']                = $this->getAttributeValue($attribute, $value, $locale, $currency);
                    $item['propertyValues'][]          = $propValue;
                }
            }

            if ($shopwareAttribute = array_search($attribute->getCode(), $attributeMapping)) {
                $attributeValue = $this->getAttributeValue($attribute, $value, $locale, $currency);
                switch ($shopwareAttribute) {
                    case 'articleNumber':
                        $item['mainDetail']['number'] = $attributeValue;
                        break;
                    case 'name':
                        $item['name'] = $attributeValue;
                        break;
                    case 'description':
                        $item['description'] = $attributeValue;
                        break;
                    case 'descriptionLong':
                        $item['descriptionLong'] = $attributeValue;
                        break;
                    case 'pseudoSales':
                        $item['pseudoSales'] = $attributeValue;
                        break;
                    case 'highlight':
                        $item['highlight'] = $attributeValue;
                        break;
                    case 'keywords':
                        $item['keywords'] = $attributeValue;
                        break;
                    case 'metaTitle':
                        $item['metaTitle'] = $attributeValue;
                        break;
                    case 'priceGroupActive':
                        $item['priceGroupActive'] = $attributeValue;
                        break;
                    case 'lastStock':
                        $item['lastStock'] = $attributeValue;
                        break;
                    case 'notification':
                        $item['notification'] = $attributeValue;
                        break;
                    case 'template':
                        $item['template'] = $attributeValue;
                        break;
                    case 'supplier':
                        $item['supplier'] = $attributeValue;
                        break;
                    case 'inStock':
                        $item['mainDetail']['inStock'] = $attributeValue;
                        break;
                    case 'stockMin':
                        $item['mainDetail']['stockMin'] = $attributeValue;
                        break;
                    case 'weight':
                        $item['mainDetail']['weight'] = $attributeValue;
                        break;
                    case 'len':
                        $item['mainDetail']['len'] = $attributeValue;
                        break;
                    case 'height':
                        $item['mainDetail']['height'] = $attributeValue;
                        break;
                    case 'ean':
                        $item['mainDetail']['ean'] = $attributeValue;
                        break;
                    case 'minPurchase':
                        $item['mainDetail']['minPurchase'] = $attributeValue;
                        break;
                    case 'purchaseSteps':
                        $item['mainDetail']['purchaseSteps'] = $attributeValue;
                        break;
                    case 'maxPurchase':
                        $item['mainDetail']['maxPurchase'] = $attributeValue;
                        break;
                    case 'purchaseUnit':
                        $item['mainDetail']['purchaseUnit'] = $attributeValue;
                        break;
                    case 'referenceUnit':
                        $item['mainDetail']['referenceUnit'] = $attributeValue;
                        break;
                    case 'packUnit':
                        $item['mainDetail']['packUnit'] = $attributeValue;
                        break;
                    case 'shippingFree':
                        $item['mainDetail']['shippingFree'] = $attributeValue;
                        break;
                    case 'releaseDate':
                        $item['mainDetail']['releaseDate'] = $attributeValue;
                        break;
                    case 'shippingTime':
                        $item['mainDetail']['shippingTime'] = $attributeValue;
                        break;
                    case 'width':
                        $item['mainDetail']['width'] = $attributeValue;
                        break;
                    case 'price':
                        $item['__options_prices']['replace'] = true;
                        $item['mainDetail']['prices'][]      = [
                            'price'            => (float)$attributeValue,
                            'customerGroupKey' => 'EK',
                        ];
                        break;
                    default:
                        if (strpos($shopwareAttribute, 'attr') !== false) {
                            if ($attributeValue != "" && $attributeValue != null
                            ) {
                                $item['mainDetail']['attribute'][$shopwareAttribute] = $attributeValue;
                            }
                        }
                        break;
                }

                if ($attribute->isLocalizable()) {
                    $localizableAttributes[$shopwareAttribute] = $attributeValue;
                    $item['localizedAttributes']               = $localizableAttributes;
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
                $options      = "";
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
                    return $price->getData();
                } else {
                    return 0;
                }
                break;
            default:
                break;
        }

        return (string)$value;
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
        if (null != $product->getAssociationForTypeCode($related)) {
            $related = $this->serializeRelated($product->getAssociationForTypeCode($related));
        }
        if (null != $product->getAssociationForTypeCode($similar)) {
            $similar = $this->serializeSimilar($product->getAssociationForTypeCode($similar));
        }

        return [$related, $similar];
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
            echo (string)$associationProduct->getIdentifier()."\n\n";
            array_push($related, [
                'number' => (string)$associationProduct->getIdentifier(),
            ]);
        }

        return $related;
    }

    /**
     * @param $product ProductInterface
     * @param $item
     * @param $attributeMapping
     * @param $currency
     *
     * @return mixed
     */
    private function createVariantGroups($product, $item, $attributeMapping, $currency, $locale, $channel)
    {
        /** @var GroupInterface $variantGroup */
        $variantGroup = $product->getVariantGroup();
        $sku          = $product->getIdentifier();

        if ($variantGroup) {
            $item['configuratorSet'] = ['groups' => []];
            $axisAttributes          = $variantGroup->getAxisAttributes();
            /** @var AttributeInterface $axis */
            foreach ($axisAttributes as $key => $axis) {
                $item['configuratorSet']['groups'][$axis->getCode()] = [
                    'name' => $axis->getLabel(),
                ];

                foreach ($axis->getOptions() as $optionKey => $option) {
                    $option->setLocale($locale);
                    $item['configuratorSet']['groups'][$axis->getCode()]['options'][$optionKey] = [
                        'name' => (string)$option,
                    ];
                }
            }

            $products         = $variantGroup->getProducts();
            $item['variants'] = [];
            foreach ($products as $key => $product) {
                if ($isMain = $sku != $product->getIdentifier()) {
                    $product->setIsVariant(true);
                    $this->entityManager->persist($product);
                }
                $product->setScope($channel);

                $item['variants'][$key] = [
                    'isMain'         => !$isMain,
                    'number'         => (string)$product->getValue($attributeMapping['articleNumber']),
                    'inStock'        => isset($attributeMapping['stock']) ? $attributeMapping['stock'] : 0,
                    'additionalText' => (string)$product->getValue($attributeMapping['name']),
                    'tax'            => 19,
                    'prices'         => [
                        [
                            'price'            => $product->getValue($attributeMapping['price']) ? (float)$product->getValue($attributeMapping['price'])->getPrice($currency)->getData() : 0,
                            'customerGroupKey' => 'EK',
                        ],
                    ],
                ];

                foreach ($item['configuratorSet']['groups'] as $groupKey => $group) {
                    $item['variants'][$key]['configuratorOptions'][$group['name']] = [
                        'group'  => $group['name'],
                        'option' => (string)$product->getValue($groupKey),
                    ];
                }
            }

            if ($variantGroup->getProductTemplate()) {
                $valuesData = $variantGroup->getProductTemplate()->getValuesData();
                if (isset($valuesData[$attributeMapping['name']])) {
                    $item['name'] = $valuesData[$attributeMapping['name']][0]['data'];
                }
            }

            $this->entityManager->flush();
        }

        return $item;
    }
}

