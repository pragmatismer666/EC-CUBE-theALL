<?php

namespace Customize\Repository;


use Customize\Entity\ShopBlog;
use Eccube\Common\Constant;
use Eccube\Common\EccubeConfig;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ShopBlogRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry, EccubeConfig $eccubeConfig)
    {
        parent::__construct($registry, ShopBlog::class);
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * @param int|null $shop_id
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderAll($shop_id = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('b')
            ->from('Customize\Entity\ShopBlog', 'b');
        if ($shop_id) {
            $qb->where('b.shop_id = :shop_id')
                ->setParameter('shop_id', $shop_id);
        }
        $qb->orderBy('b.publish_date', 'DESC')
            ->addOrderBy('b.id', 'DESC');

        return $qb;
    }

    /**
     * @param int $shop_id
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDisplayShopBlogs($shop_id)
    {
        return $this->createQueryBuilder('b')
            ->where('b.shop_id = :shop_id')
            ->setParameter('shop_id', $shop_id)
            ->andWhere('b.visible = :visible')
            ->setParameter('visible', Constant::ENABLED)
            ->orderBy('b.publish_date', 'DESC')
            ->addOrderBy('b.id', 'DESC')
            ->setMaxResults($this->eccubeConfig->get('malldevel.display_blog_count'))
            ->getQuery()
            ->getResult();
    }

    /**
     * @param ShopBlog $ShopBlog
     * @return array
     */
    public function getNextPrevId(ShopBlog $ShopBlog)
    {
        $Prev = $this->createQueryBuilder('b')
            ->where('b.shop_id = :shop_id')
            ->setParameter('shop_id', $ShopBlog->getShopId() ?? null)
            ->andWhere('b.visible = :visible')
            ->setParameter('visible', Constant::ENABLED)
            ->andWhere('b.publish_date <= :publish_date')
            ->setParameter('publish_date', $ShopBlog->getPublishDate())
            ->andWhere('b.id != :id')
            ->setParameter('id', $ShopBlog->getId())
            ->orderBy('b.publish_date', 'DESC')
            ->addOrderBy('b.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        $Next = $this->createQueryBuilder('b')
            ->where('b.shop_id = :shop_id')
            ->setParameter('shop_id', $ShopBlog->getShopId() ?? null)
            ->andWhere('b.visible = :visible')
            ->setParameter('visible', Constant::ENABLED)
            ->andWhere('b.publish_date >= :publish_date')
            ->setParameter('publish_date', $ShopBlog->getPublishDate())
            ->andWhere('b.id != :id')
            ->setParameter('id', $ShopBlog->getId())
            ->orderBy('b.publish_date', 'ASC')
            ->addOrderBy('b.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        return [
            'prev_id' => $Prev ? $Prev->getId() : null,
            'next_id' => $Next ? $Next->getId() : null,
        ];
    }
}