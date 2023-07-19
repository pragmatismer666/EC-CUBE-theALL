<?php

namespace Customize\Services;

use Eccube\Entity\Product;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckAction {

    protected $container;
    protected $entityManager;
    protected $orderRepository;
    protected $orderItemRepository;
    
    public function __construct(
        ContainerInterface $container
    )
    {
        $this->container = $container;
        $this->entityManager = $this->container->get('doctrine.orm.entity_manager');
        $this->orderRepository = $this->entityManager->getRepository(Order::class);
        $this->orderItemRepository = $this->entityManager->getRepository(OrderItem::class);
    }

    public function checkIfProductRelatedWithOrders($Product)
    {
        $relatedItem = $this->orderItemRepository->findOneBy(['Product' => $Product]);
        if (empty($relatedItem)) return null;
        return $relatedItem->getOrder();
    }
    
}