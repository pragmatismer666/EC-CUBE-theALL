<?php

namespace Customize\Repository;

use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Eccube\Util\StringUtil;
use Customize\Entity\Katakana;

class KatakanaRepository extends AbstractRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct( $registry, Katakana::class );
    }

    public function get( $id = 1 ) {
        return $this->find($id);
    }
    public function getByChar($char) {
        $query = $this->createQueryBuilder('k')
            ->select('k')
            ->where('k.character LIKE :chara')
            ->setParameter('chara', $char)
            ->getQuery();
        $res = $query->getResult();
        if( !$res ) {
            return null;
        }
        return $res[0];
    }
}