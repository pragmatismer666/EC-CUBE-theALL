<?php

namespace Customize\Repository;

use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Eccube\Util\StringUtil;
use Customize\Entity\ShopCategory;
use Eccube\Entity\Category;

class ShopCategoryRepository extends AbstractRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct( $registry, ShopCategory::class );
    }

    public function get( $id = 1 ) {
        return $this->find($id);
    }
    
}