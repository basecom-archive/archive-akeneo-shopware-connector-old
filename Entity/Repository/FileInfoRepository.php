<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Entity\Repository;

use Akeneo\Bundle\FileStorageBundle\Doctrine\ORM\Repository\FileInfoRepository as BaseRepository;

class FileInfoRepository extends BaseRepository
{
    public function findMediaIdsNotInProducts($inProduct, $swIds)
    {
        $qb = $this->createQueryBuilder('FileInfo');
        $qb->select('FileInfo.swMediaId', 'FileInfo.id');
        $qb->andWhere(
            $qb->expr()->isNotNull('FileInfo.swMediaId')
        );
        $qb->andWhere(
            $qb->expr()->in('FileInfo.swMediaId', $swIds)
        );

        if(!empty($inProduct)) {
            $qb->andWhere(
                $qb->expr()->notIn('FileInfo.swMediaId', $inProduct)
            );
        }

        return $qb->getQuery()->getResult();
    }

//    public function findMediaIdsInProductAssets()
//    {
//        $result = $this->getEntityManager()->getConnection()->executeQuery(
//            'SELECT fileinfo.swMediaId FROM akeneo_file_storage_file_info fileinfo
//              LEFT JOIN pimee_product_asset_variation av ON fileinfo.id = av.file_info_id
//              LEFT JOIN pimee_product_asset_reference ref ON av.reference_id = ref.id
//              LEFT JOIN pim_catalog_product_value_asset valas ON valas.asset_id = ref.asset_id
//              LEFT JOIN pim_catalog_product_value prodval ON prodval.id = valas.value_id
//              LEFT JOIN pim_catalog_product prod ON prod.id = prodval.entity_id
//              WHERE fileinfo.swMediaId IS NOT NULL AND prod.swProductId IS NOT NULL'
//        )
//            ->fetchAll();
//
//        return $result;
//    }

    public function findAllAssetFileInfo()
    {
        $qb = $this->createQueryBuilder('FileInfo');
        $qb->select('FileInfo.swMediaId');
        $qb->andWhere(
            $qb->expr()->like('FileInfo.storage', $qb->expr()->literal('assetStorage'))
        );
        $qb->andWhere(
            $qb->expr()->isNotNull('FileInfo.swMediaId')
        );

        return $qb->getQuery()->getResult();
    }
}