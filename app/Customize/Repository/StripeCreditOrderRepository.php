<?php
namespace Customize\Repository;

use Eccube\Repository\AbstractRepository;
use Eccube\Util\StringUtil;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Customize\Entity\StripeCreditOrder;

class StripeCreditOrderRepository extends AbstractRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct( $registry, StripeCreditOrder::class );
    }

    public function get( $id = 1 ) {
        return $this->find($id);
    }
}