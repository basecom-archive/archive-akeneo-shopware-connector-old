<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Entity\Repository;

use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository as BaseRepository;

class ProductRepository extends BaseRepository
{
    /**
     * {@inheritdoc}
     */
    public function findIdByNotInSwId(array $swIds)
    {
        $qb = $this->createQueryBuilder('Product');
        $this->addJoinToValueTables($qb);
        $rootAlias = current($qb->getRootAliases());
        $qb->select($rootAlias.'.swProductId');
        $qb->andWhere(
            $qb->expr()->in($rootAlias.'.swProductId', ':swIds')
        );
        $qb->addGroupBy($rootAlias.'.id');
        $qb->setParameter(':swIds', $swIds);

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    public function findProductMediaWithSwId()
    {
        $qb = $this->createQueryBuilder('Product');
        $this->addJoinToValueTables($qb);
        $qb->select('FileInfo.swMediaId');
        $qb->leftJoin('Value.media', 'FileInfo');
        $qb->andWhere(
            $qb->expr()->isNotNull('FileInfo.swMediaId')
        );
        $qb->andWhere(
            $qb->expr()->isNotNull('Product.swProductId')
        );

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    public function findOneBySwId($swId)
    {
        $pqb = $this->queryBuilderFactory->create();
        $pqb->addFilter('swProductId', '=', $swId);
        $qb = $pqb->getQueryBuilder();
        $result = $qb->getQuery()->execute();

        if (empty($result)) {
            return null;
        }

        return reset($result);
    }
}