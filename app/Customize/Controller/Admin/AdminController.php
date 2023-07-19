<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Controller\Admin;

use Eccube\Controller\Admin\AdminController as ParentController;
use Eccube\Entity\Member;
use Eccube\Entity\Master\OrderStatus;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\NoResultException;

class AdminController extends ParentController
{
    protected $excludes = [OrderStatus::CANCEL, OrderStatus::PENDING, OrderStatus::PROCESSING, OrderStatus::RETURNED];
    
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param array $excludes
     *
     * @return null|Request
     */
    protected function getOrderEachStatus(array $excludes)
    {
        $res = $this->getShopAndMember();
        extract($res);
        if ($Member->getRole() == "ROLE_SHOP_OWNER" && empty($Shop)) {
            return [];
        }

        $sql = "SELECT
                    t1.order_status_id as status,
                    COUNT(t1.id) as count
                FROM
                    dtb_order t1
                WHERE
                    t1.order_status_id NOT IN (:excludes) "
                . (empty($Shop) ? "" : (" and t1.shop_id = ". $Shop->getId()))
                . " GROUP BY
                    t1.order_status_id
                ORDER BY
                    t1.order_status_id";
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('status', 'status');
        $rsm->addScalarResult('count', 'count');
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $query->setParameters([':excludes' => $excludes]);
        $result = $query->getResult();
        $orderArray = [];
        foreach ($result as $row) {
            $orderArray[$row['status']] = $row['count'];
        }

        return $orderArray;
    }

    /**
     * @param $dateTime
     *
     * @return array|mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getSalesByDay($dateTime)
    {
        // concat... for pgsql
        // http://stackoverflow.com/questions/1091924/substr-does-not-work-with-datatype-timestamp-in-postgres-8-3
        $res = $this->getShopAndMember();
        extract($res);
        if ($Member->getRole() == "ROLE_SHOP_OWNER" && empty($Shop)) {
            return [];
        }

        $dql = 'SELECT
                  SUBSTRING(CONCAT(o.order_date, \'\'), 1, 10) AS order_day,
                  SUM(o.payment_total) AS order_amount,
                  COUNT(o) AS order_count
                FROM
                  Eccube\Entity\Order o
                WHERE
                    o.OrderStatus NOT IN (:excludes)
                    AND SUBSTRING(CONCAT(o.order_date, \'\'), 1, 10) = SUBSTRING(:targetDate, 1, 10) '
                . (empty($Shop) ? "" :( " and o.Shop = :Shop")).
                ' GROUP BY
                  order_day';

        $q = $this->entityManager
            ->createQuery($dql)
            ->setParameter(':excludes', $this->excludes)
            ->setParameter(':targetDate', $dateTime);
        $q = empty($Shop) ? $q : $q->setParameter(":Shop", $Shop);

        $result = [];
        try {
            $result = $q->getSingleResult();
        } catch (NoResultException $e) {
            // 結果がない場合は空の配列を返す.
        }

        return $result;
    }
    /**
     * @param $dateTime
     *
     * @return array|mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getSalesByMonth($dateTime)
    {
        // concat... for pgsql
        // http://stackoverflow.com/questions/1091924/substr-does-not-work-with-datatype-timestamp-in-postgres-8-3
        $res = $this->getShopAndMember();
        extract($res);
        if ($Member->getRole() == "ROLE_SHOP_OWNER" && empty($Shop)) {
            return [];
        }
        $dql = 'SELECT
                  SUBSTRING(CONCAT(o.order_date, \'\'), 1, 7) AS order_month,
                  SUM(o.payment_total) AS order_amount,
                  COUNT(o) AS order_count
                FROM
                  Eccube\Entity\Order o
                WHERE
                    o.OrderStatus NOT IN (:excludes)
                    AND SUBSTRING(CONCAT(o.order_date, \'\'), 1, 7) = SUBSTRING(:targetDate, 1, 7) '
                . (empty($Shop) ? "" : (" and o.Shop = :Shop")) .
                ' GROUP BY
                  order_month';

        $q = $this->entityManager
            ->createQuery($dql)
            ->setParameter(':excludes', $this->excludes)
            ->setParameter(':targetDate', $dateTime);
        $q = empty($Shop) ? $q : $q->setParameter(":Shop", $Shop);

        $result = [];
        try {
            $result = $q->getSingleResult();
        } catch (NoResultException $e) {
            // 結果がない場合は空の配列を返す.
        }

        return $result;
    }


    protected function getShopAndMember()
    {
        $Member = $this->getUser();

        if (!($Member instanceof Member)) {
            throw new NotFoundHttpException("Bad request");
        }
        return ['Shop' => $Member->getShop(), 'Member' => $Member];
        
    }
}
