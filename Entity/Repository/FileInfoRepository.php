<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Entity\Repository;

use Akeneo\Bundle\FileStorageBundle\Doctrine\ORM\Repository\FileInfoRepository as BaseRepository;

/**
 * Class FileInfoRepository
 * @package Basecom\Bundle\ShopwareConnectorBundle\Entity\Repository
 */
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

        if (!empty($inProduct)) {
            $qb->andWhere(
                $qb->expr()->notIn('FileInfo.swMediaId', $inProduct)
            );
        }

        return $qb->getQuery()->getResult();
    }

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
