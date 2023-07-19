<?php

namespace Customize\Repository;

use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Eccube\Util\StringUtil;
use Customize\Entity\Feature;

class FeatureRepository extends AbstractRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct( $registry, Feature::class );
    }

    public function get( $id = 1 ) {
        return $this->find($id);
    }
    public function getBySearchQuery($searchData)
    {
        
        $qb = $this->createQueryBuilder('f')
            ->select('f, fp, p')
            ->leftJoin('f.FeatureProducts', 'fp')
            ->leftJoin('fp.Product', 'p');
        if (isset($searchData['multi']) && StringUtil::isNotBlank($searchData['multi'])) {
            $multi = preg_match('/^\d{0,10}$/', $searchData['multi']) ? $searchData['multi'] : null;
            $qb
                ->andWhere('f.id = :multi OR f.title LIKE :likemulti OR f.content LIKE :likemulti')
                ->setParameter('multi', $multi)
                ->setParameter('likemulti', '%' . $searchData['multi'] . '%');
        }
        if (isset($searchData['visible']) && !\is_null($searchData['visible']) && count($searchData['visible']) > 0) {
            $qb
                ->andWhere($qb->expr()->in('f.visible', ':visible'))
                ->setParameter('visible', $searchData['visible']);
        }
        if (!empty($searchData['Shop'])) {
            $qb
                ->leftJoin("f.FeatureShops", 'ffs')
                ->andWhere('ffs.Shop = :Shop')
                ->setParameter('Shop', $searchData['Shop']);
        }
        $qb->orderBy('f.create_date', 'DESC');
        $qb->addorderBy('f.id', 'DESC');
        
        return $qb;
    }
    public function getShownList()
    {
        $qb = $this->createQueryBuilder('f')
                    ->select('f')
                    ->where('f.visible = :visible')
                    ->setParameter('visible', true);
        return $qb->getQuery()->getResult();
    }
}