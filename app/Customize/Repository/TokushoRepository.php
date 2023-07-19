<?php

namespace Customize\Repository;

use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Eccube\Util\StringUtil;
use Customize\Entity\Tokusho;
use Eccube\Doctrine\Query\Queries;

class TokushoRepository extends AbstractRepository {

    protected $queries;
    
    
    public function __construct(
        RegistryInterface $registry,
        Queries $queries
    ) {
        parent::__construct( $registry, Tokusho::class );
        $this->queries = $queries;
    
    }

    public function get( $id = 1 ) {
        return $this->find($id);
    }
}