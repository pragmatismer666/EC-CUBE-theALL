<?php
namespace Customize\Repository;

use Eccube\Repository\AbstractRepository;
use Eccube\Util\StringUtil;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Customize\Entity\ShopIdentityDoc;

class ShopIdentityDocRepository extends AbstractRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct( $registry, ShopIdentityDoc::class );
    }

    public function get( $id = 1 ) {
        return $this->find($id);
    }
}