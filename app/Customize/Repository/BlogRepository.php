<?php

namespace Customize\Repository;

use Customize\Entity\Blog;
use Customize\Entity\Master\BlogType;
use Eccube\Common\Constant;
use Eccube\Common\EccubeConfig;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BlogRepository extends AbstractRepository
{

    /** @var  EccubeConfig */
    protected $eccubeConfig;

    public function __construct(
        RegistryInterface $registry,
        EccubeConfig $eccubeConfig
    )
    {
        parent::__construct($registry, Blog::class);
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * @param array|null $searchData
     */
    public function getQueryBuilderBySearchDataForAdmin($searchData = null)
    {
        $qb = $this->createQueryBuilder('b')
            ->addSelect('bt', 'c')
            ->innerJoin('b.BlogType', 'bt')
            ->innerJoin('b.Category', 'c');
        if (!empty($searchData['blog_type_id'])) {
            $qb->andWhere($qb->expr()->in('bt.id', ':blog_type_id'))
                ->setParameter('blog_type_id', $searchData['blog_type_id']);
        }
        if (!empty($searchData['category_id'])) {
            $category_id = $searchData['category_id'];
            $qb->andWhere('c.id = :category_id')
                ->setParameter('category_id', $category_id);
        }
        if (!empty($searchData['visible'])) {
            $qb->andWhere($qb->expr()->in('b.visible', ':visible'))
                ->setParameter('visible', $searchData['visible']);
        }
        $qb->orderBy('b.publish_date', 'DESC')
            ->addOrderBy('b.id', 'DESC');

        return $qb;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDisplayNotices()
    {
        return $this->createQueryBuilder('b')
            ->addSelect('c', 'bt')
            ->innerJoin('b.Category', 'c')
            ->innerJoin('b.BlogType', 'bt')
            ->andWhere('b.visible = :visible')
            ->setParameter('visible', Constant::ENABLED)
            ->andWhere('bt.id = :blog_type_id')
            ->setParameter('blog_type_id', BlogType::NOTICE)
            ->orderBy('b.publish_date', 'DESC')
            ->addOrderBy('b.id', 'DESC')
            ->setMaxResults($this->eccubeConfig->get('malldevel.display_blog_count'))
            ->getQuery()
            ->getResult();
    }

    public function getNextPrevId(Blog $Blog)
    {
        $blogTypeId = null;
        try {
            $blogTypeId = $Blog->getBlogType()->getId();
        } catch (\Exception $e) {

        }
        $Prev = $this->createQueryBuilder('b')
            ->addSelect('bt')
            ->innerJoin('b.BlogType', 'bt')
            ->andWhere('bt.id = :blog_type_id')
            ->setParameter('blog_type_id', $blogTypeId)
            ->andWhere('b.visible = :visible')
            ->setParameter('visible', Constant::ENABLED)
            ->andWhere('b.publish_date <= :publish_date')
            ->setParameter('publish_date', $Blog->getPublishDate())
            ->andWhere('b.id != :id')
            ->setParameter('id', $Blog->getId())
            ->orderBy('b.publish_date', 'DESC')
            ->addOrderBy('b.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        $Next = $this->createQueryBuilder('b')
            ->addSelect('bt')
            ->innerJoin('b.BlogType', 'bt')
            ->andWhere('bt.id = :blog_type_id')
            ->setParameter('blog_type_id', $blogTypeId)
            ->andWhere('b.visible = :visible')
            ->setParameter('visible', Constant::ENABLED)
            ->andWhere('b.publish_date >= :publish_date')
            ->setParameter('publish_date', $Blog->getPublishDate())
            ->andWhere('b.id != :id')
            ->setParameter('id', $Blog->getId())
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

    /**
     * @param array $searchData
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getList($searchData)
    {
        $qb = $this->createQueryBuilder('b')
            ->addSelect('bt', 'c', 't')
            ->innerJoin('b.BlogType', 'bt')
            ->leftJoin('b.BlogTags', 't')
            ->innerJoin('b.Category', 'c')
            ->andWhere('bt.id = :blog_type_id')
            ->setParameter('blog_type_id', $searchData['blog_type_id'] ?? BlogType::NOTICE)
            ->andWhere('b.visible = :visible')
            ->setParameter('visible', Constant::ENABLED);
        if (!empty($searchData['category_id'])) {
            $qb->andWhere('c.id = :category_id')
                ->setParameter('category_id', $searchData['category_id']);
        }
        if (!empty($searchData['tag_id'])) {
            $qb->andWhere('t.tag_id = :tag_id')
                ->setParameter('tag_id', $searchData['tag_id']);
        }
        return $qb->orderBy('b.publish_date', 'DESC')
            ->addOrderBy('b.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
