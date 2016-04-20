<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Serializer;

use Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository\CategoryRepository;
use Akeneo\Bundle\FileStorageBundle\Doctrine\ORM\Repository\FileInfoRepository;
use Basecom\Bundle\ShopwareConnectorBundle\Api\ApiClient;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Category;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Family;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\FileInfo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\FamilyRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Component\Catalog\Model\Association;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductValueInterface;


class ShopwareProductSerializer
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var FamilyRepository */
    protected $familyRepository;

    /** @var ProductRepository */
    protected $productRepository; // Todo: Check if needed

    /** @var AttributeOptionRepository */
    protected $attributeOptionRepository;// Todo: Check if needed

    /** @var CategoryRepository */
    protected $categoryRepository;

    /** @var FileInfoRepository */
    protected $fileInfoRepository;

    /** @var EntityManager */
    protected $entityManager;

    protected $rootDir;

    /**
     * ShopwareProductSerializer constructor.
     * @param AttributeRepository $attributeRepository
     * @param FamilyRepository $familyRepository
     */
    public function __construct(AttributeRepository $attributeRepository,
                                FamilyRepository $familyRepository,
                                ProductRepository $productRepository,
                                AttributeOptionRepository $attributeOptionRepository,
                                CategoryRepository $categoryRepository,
                                $rootDir,
                                FileInfoRepository $fileInfoRepository,
                                EntityManager $entityManager
    )
    {
        $this->productRepository    = $productRepository;
        $this->attributeRepository  = $attributeRepository;
        $this->familyRepository     = $familyRepository;
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->categoryRepository   = $categoryRepository;
        $this->rootDir              = $rootDir;
        $this->fileInfoRepository   = $fileInfoRepository;
        $this->entityManager        = $entityManager;
    }

    public function serialize(Product $product, $attributeMapping, $locale, $filterAttributes, ApiClient $apiClient) {
        // echo "Product Serializer for Product ".$product->getReference()."...".$this->rootDir[0]."\n";
        // echo "Produkt ID: ".$product->getId()."\n\n";
        //$similar = $attributeMapping['similar'];
        //$related = $attributeMapping['related'];
        //unset($attributeMapping['similar']);
        //unset($attributeMapping['related']);

        $item = $this->serializeValues($product->getValues(), $product->getAttributes(), $attributeMapping, $locale, $apiClient, $filterAttributes);
        $item['mainDetail']['active']   = $item['active'] = $product->isEnabled();
        $item['categories']             = $this->serializeCategories($product->getCategories());
//        $associations                   = $this->serializeAssociations($product, $similar, $related);
//        $item['similar']                = $associations['similar'];
//        $item['related']                = $associations['related'];
        $item['taxId']                  = 1;
//        $item['mainDetail']['attribute']['attr1'] = "FreitextFreitextFreitextFreitextFreitextFreitext";
        if($product->getFamily() != null) {
            $propertyGroup = $this->serializeFamily($product->getFamily()->getId());
            $item['filterGroupId']          = $propertyGroup['id'];
//            var_dump($item['filterGroupId']);
//            //$item['propertyGroup']          = $propertyGroup;
//            $item['propertyValues']         = $this->serializePropertyValues($product->getValues(), $filterAttributes);//$this->serializeValuess($product->getValues(), $product->getAttributes(), $attributeMapping);//
//            var_dump($item['propertyValues']);
        }
        $item = $this->setRequiredValues($item);
//        if(isset($item['propertyValues'])) var_dump($item['propertyValues']);
//        var_dump($item['propertyValues']);
        if(isset($item['name']) && $item['name'] == "Sony SRS-BTV25") {
            echo "-----------!-!-!-!".$item['name']."\n";
            var_dump($item['mainDetail']);
        }
        return $item;
    }

    public function setRequiredValues($item) {
        if(!isset($item['supplier']) || $item['supplier'] == null) $item['supplier'] = "/";
//        if(!isset($item['mainDetail']['prices']) || $item['mainDetail']['prices'] == null) $item['mainDetail']['prices'] = array(array('price' => 0));
        if($item['taxId'] == null) $item['taxId'] = 1;
        return $item;
    }

    protected function serializeCategories($productCategories) {
        $categories = array();
        foreach($productCategories as $category) {
            /** @var Category $category */
            $category = $this->categoryRepository->find($category->getId());
            $category->setLocale('en_US');
            $categories[$category->getSid()] = array(
                'id'    => $category->getSid(),
                'name'  => $category->getLabel(),
            );
        }
        return $categories;
    }

    public function serializeAttributes($attributes) {
        $attributeArray = array();
        /** @var Attribute $attribute */
        foreach($attributes as $attribute) {
            $attribute = $this->attributeRepository->find($attribute->getId());
            array_push($attributeArray, $attribute->getCode());
        }
        return $attributeArray;
    }

    public function serializeFamily($familyId) {
        /** @var Family $family */
        $family = $this->familyRepository->find($familyId);
        $family->setLocale("en_US");
        $propertyGroup = array(
            'id'  => (int)$family->getSid(),
            'name' => $family->getLabel(),
        );
        return $propertyGroup;
    }

    protected function serializeFilterAttributes($filterAttributes) {
        $filterAttributes = str_replace(' ', '', $filterAttributes);
        $filterAttributesArray = explode(',', $filterAttributes);
        return $filterAttributesArray;
    }

    public function serializeValues($values, $attributes, $attributeMapping, $locale, ApiClient $apiClient, $filterAttributes) {
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
                        $path = $this->rootDir[0]."/file_storage/catalog/".$value->getMedia()->getKey();
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
                        $propValue['value'] = $this->getAttributeValue($attribute, $value);
                        $item['propertyValues'][$propValueCount] = $propValue;
                        $propValueCount++;
                    }
                }

                if($shopwareAttribute = array_search($attribute->getCode(), $attributeMapping)) {
                    switch($shopwareAttribute) {
                        case 'articleNumber':
                            $item['mainDetail']['number'] = $this->getAttributeValue($attribute, $value);
                            break;
                        case 'name':
                            $item['name'] = $this->getAttributeValue($attribute, $value);
                            break;
                        case 'description':
                            $item['description'] = $this->getAttributeValue($attribute, $value);
                            break;
                        case 'keywords':
                            $item['keywords'] = $this->getAttributeValue($attribute, $value);
                            break;
                        case 'descriptionLong':
                            $item['descriptionLong'] = $this->getAttributeValue($attribute, $value);
                            break;
                        case 'pseudoSales':
                            $item['pseudoSales'] = $this->getAttributeValue($attribute, $value);
                            break;
                        case 'highlight':
                            $item['highlight'] = $this->getAttributeValue($attribute, $value);
                            break;
                        case 'priceGroupActive':
                            $item['priceGroupActive'] = $this->getAttributeValue($attribute, $value);
                            break;
                        case 'notification':
                            $item['notification'] = $this->getAttributeValue($attribute, $value);
                            break;
                        case 'inStock':
                            $item['mainDetail']['inStock'] = $this->getAttributeValue($attribute, $value);
                            break;
                        case 'price':
                            $item['mainDetail']['prices'] = $this->getAttributeValue($attribute, $value);
                            break;
                        case 'metaTitle':
                            $item['metaTitle'] = $this->getAttributeValue($attribute, $value);
                            break;
                        case 'supplier':
                            $item['supplier'] = $this->getAttributeValue($attribute, $value);
                            echo $item['supplier'];
                            break;
                        default:
                            echo "default\n";
                            // Todo: richtige Überprüfung machen !!
                            if(strpos($shopwareAttribute, 'attr')) {
                                $item['mainDetail']['attributes'][$shopwareAttribute] = $this->getAttributeValue($attribute, $value);
                                echo "shopwareAttribute: ".$shopwareAttribute."\n";
                            }
                            //$item['mainDetail']['attribute']['attr1'] = $this->getAttributeValue($attribute, $value);
                            //Todo:
                            //if filterAttribute
                            //do filterAttributeStuff
                            //else
                            //do zusätzlich AttributeStuff
                            //$item['mainDetail']['attribute']['attr'.$attrCount] = $this->getAttributeValue($attribute, $value);

                            break;
                    }
                }
            }
        }
        $this->entityManager->flush();
        return $item;
    }

    protected function getAttributeValue(Attribute $attribute, ProductValueInterface $value) {
        switch($attribute->getBackendType()) {
            case 'options':
                $options = "";
                $optionsCount = 0;
                foreach($value->getOptions() as $option) {
                    $option->setLocale("en_US");
                    if($optionsCount > 0) $options .= ", ";
                    $options .= $option->getOptionValue()->getValue();
                    $optionsCount++;
                }
                return $options;
                break;
            case 'option':
                $option = $value->getOption();
                $option->setLocale("en_US");
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
            case 'media':
                //echo "Media ID: ".$value->getMedia()->getId()."\nMedia Extension: ".$value->getMedia()->getExtension()."\nMedia Hash: ".$value->getMedia()->getHash().
                //    "\nMedia Key: ".$value->getMedia()->getKey()."\nMedia MimeType: ".$value->getMedia()->getMimeType()."\nMedia Original Filename".$value->getMedia()->getOriginalFilename().
                //    "\nMedia Size: ".$value->getMedia()->getSize()."\nMedia Storage: ".$value->getMedia()->getStorage()."\nMedia Uploaded File";
                break;
            case 'prices':
                return array(
                    array(
                        'price' => $value->getPrice('EUR')->getData(),
                    ));
                break;
            default:
                break;
        }
        return null;
    }

    /**
     * @param Product $product
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
     */
    protected function serializeSimilar($association) {
        $similar = [];
        if($association === null) return $similar;
        foreach($association->getProducts() as $associationProduct) {
            array_push($similar, array(
                'number'    => (string)$associationProduct->getIdentifier(),
                'name'      => $this->getProductName($associationProduct),
            ));
        }
        return $similar;
    }

    /**
     * @param Association $association
     */
    protected function serializeRelated($association) {
        $related = [];
        if($association === null) return $related;
        foreach($association->getProducts() as $associationProduct) {
            array_push($related, array(
                'number'    => (string)$associationProduct->getIdentifier(),
                'name'  => $this->getProductName($associationProduct),
            ));
        }
        return $related;
    }

    /**
     * @param Product $product
     */
    protected function getProductName($product) { // todo: noch auf mapping umbauen
        $attributes = $this->serializeAttributes($product->getAttributes());
        $values = $product->getValues();
        $productName = "";
        /** @var ProductValueInterface $value */
        foreach($values as $value) {
            if(in_array($value->getAttribute()->getCode(), $attributes)) {
                $attribute = $this->attributeRepository->find($value->getAttribute()->getId());
                $attribute->setLocale('en_US');
                if($attribute->getLabel() == "Name") {
                    $productName = $value->getVarchar();
                    break;
                }
            }
        }
        return $productName;
    }
}