<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Serializer;

use Akeneo\Bundle\FileStorageBundle\Doctrine\ORM\Repository\FileInfoRepository;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\Attribute;
use Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository\CategoryRepository;
use Basecom\Bundle\ShopwareConnectorBundle\Entity\FileInfo;
use MongoDBODMProxies\__CG__\Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\ProductRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\FamilyRepository;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;


class ShopwareProductImageSerializer
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var FamilyRepository */
    protected $familyRepository;

    /** @var ProductRepository */
    protected $productRepository; // Todo: Check if needed

    /** @var CategoryRepository */
    protected $categoryRepository;

    /** @var FileInfoRepository */
    protected $fileInfoRepository;

    protected $rootDir;

    /**
     * ShopwareProductImageSerializer constructor.
     * @param AttributeRepository $attributeRepository
     * @param FamilyRepository $familyRepository
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(AttributeRepository $attributeRepository, FamilyRepository $familyRepository, ProductRepository $productRepository, CategoryRepository $categoryRepository, $rootDir, FileInfoRepository $fileInfoRepository)
    {
        $this->attributeRepository  = $attributeRepository;
        $this->familyRepository     = $familyRepository;
        $this->productRepository    = $productRepository;
        $this->categoryRepository   = $categoryRepository;
        $this->rootDir              = $rootDir;
        $this->fileInfoRepository   = $fileInfoRepository;
    }

    public function serialize(Product $product) {
        $item = array();
        $attributes = $this->serializeAttributes($product->getAttributes());

        $valueCount = 0;
        $articleNumber = "";
        /** @var ProductValueInterface $value */
        foreach($product->getValues() as $value) {
            if($value->getAttribute()->getCode() == "sku") {
                $articleNumber = $value->getVarchar();
            } elseif(in_array($value->getAttribute()->getCode(), $attributes)) {
                $item['article_number'] = $articleNumber;
                $item['image_path'][$valueCount] = $this->rootDir[0]."/file_storage/catalog/".$value->getMedia()->getKey();
                echo "\n\n------------------------Media id for $articleNumber: ".$value->getMedia()->getId()."\n";

//                $mediaId = $value->getMedia()->getId();
//                /** @var FileInfo $media */
//                $media = $this->fileInfoRepository->find($mediaId);
//                var_dump($media->getSwMediaId());
                $valueCount++;
            }
        }
        return $item;
    }

    protected function serializeAttributes($attributes) {
        $attributeArray = array();
        /** @var Attribute $attribute */
        foreach($attributes as $attribute) {
            if("pim_catalog_image" == $attribute->getAttributeType()) {
                array_push($attributeArray, $attribute->getCode());
            }
        }
        return $attributeArray;
    }
}