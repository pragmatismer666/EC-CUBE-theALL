<?php

namespace Customize\Services;

use Customize\Entity\Mapped\CustomerHistory;
use Customize\Entity\ProductCustomerHistory;
use Customize\Entity\Shop;
use Customize\Entity\ShopCustomerHistory;
use Customize\Repository\ProductCustomerHistoryRepository;
use Customize\Repository\ShopCustomerHistoryRepository;
use Customize\Util\Collection;
use Doctrine\Common\Collections\Collection as CollectionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Customer;
use Eccube\Entity\Product;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CustomerHistoryService
{
    /** @var  Session */
    protected $session;

    /** @var  TokenStorageInterface */
    protected $tokenStorage;

    /** @var  ProductCustomerHistoryRepository */
    protected $productHistoryRepository;

    /** @var  ShopCustomerHistoryRepository */
    protected $shopHistoryRepository;

    /** @var  EccubeConfig */
    protected $eccubeConfig;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    /** @var int */
    protected $maxCount = 0;

    const SESSION_KEY_SHOP_HISTORY = 'malldevel.customer_history.shop';
    const SESSION_KEY_PRODUCT_HISTORY = 'malldevel.customer_history.product';

    public function __construct(
        Session $session,
        TokenStorageInterface $tokenStorage,
        ProductCustomerHistoryRepository $productHistoryRepository,
        ShopCustomerHistoryRepository $shopHistoryRepository,
        EccubeConfig $eccubeConfig,
        EntityManagerInterface $entityManager
    )
    {
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->productHistoryRepository = $productHistoryRepository;
        $this->shopHistoryRepository = $shopHistoryRepository;
        $this->eccubeConfig = $eccubeConfig;
        $this->maxCount = $this->eccubeConfig->get('malldevel.customer_history.max') ?? 0;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Shop $Shop
     * @return void
     */
    public function saveShopHistory(Shop $Shop)
    {
        $Customer = $this->getCustomer();
        if ($Customer instanceof Customer) {
            $this->saveCustomerShopHistory($Shop, $Customer);
        }
        $this->saveGuestShopHistory($Shop);
    }

    /**
     * @param Product $Product
     * @return void
     */
    public function saveProductHistory(Product $Product)
    {
        $Customer = $this->getCustomer();
        if ($Customer instanceof Customer) {
            $this->saveCustomerProductHistory($Product, $Customer);
        }
        $this->saveGuestProductHistory($Product);
    }

    /**
     * @return Collection
     */
    public function getShopHistory()
    {
        try {
            $History = $this->getGuestShopHistory();
            return $this->getDisplayHistory($History);
        } catch (\Exception $e) {
            log_error($e->getMessage());
            return new Collection();
        }
    }

    /**
     * @return Collection
     */
    public function getProductHistory()
    {
        try {
            $History = $this->getGuestProductHistory();
            return $this->getDisplayHistory($History, true);
        } catch (\Exception $e) {
            log_error($e->getMessage());
            return new Collection();
        }
    }

    /**
     * @param Collection $History
     * @param bool $removeLast
     * @return Collection
     */
    protected function getDisplayHistory($History, $removeLast = false)
    {
        $History = Collection::from($History);
        $History = $History->sort(function ($a, $b) {
            return $a->getCreateDate() > $b->getCreateDate() ? -1 : 1;
        });
        if ($removeLast && $History->last()) {
            $History->removeElement($History->last());
        }
        if ($this->maxCount) {
            return $History->slice(-1 * $this->maxCount);
        } else {
            return $History;
        }
    }

    /**
     * @return Collection
     */
    protected function getGuestShopHistory()
    {
        /** @var Collection $History */
        return $this->session->get(self::SESSION_KEY_SHOP_HISTORY, new Collection());
    }

    /**
     * @param Customer $Customer
     * @return Collection
     */
    protected function getCustomerShopHistory(Customer $Customer)
    {
        return Collection::from($this->shopHistoryRepository->getCustomerHistory($Customer));
    }

    public function saveGuestHistoryToCustomerHistory()
    {
        /** @var $Customer */
        $Customer = $this->getCustomer();
        if (!$Customer || !$Customer instanceof Customer) {
            return;
        }
        $ProductHistories = $this->getGuestProductHistory()
            ->merge($this->getCustomerProductHistory($Customer));

        $ProductHistories = $ProductHistories->sort(function($a, $b) {
            return $a->getCreateDate() > $b->getCreateDate() ? -1 : 1;
        })->unique(function ($item /** @var ProductCustomerHistory $item */) {
            if (!$item->getProduct()) {
                return null;
            }
            return $item->getProduct()->getId();
        }, function ($a, $b) {
            return $a->getCreateDate() > $b->getCreateDate() ? 1 : -1;
        });
        $SaveHistories = [];
        foreach($ProductHistories as $ProductHistory) {
            if (empty($ProductHistory->getProduct()->getId())) {
                continue;
            }
            $Product = $this->entityManager->getRepository('Eccube\Entity\Product')
                ->find($ProductHistory->getProduct()->getId());
            if (empty($Product)) {
                continue;
            }
            $New = new ProductCustomerHistory();
            $New->setCustomer($Customer);
            $New->setProduct($Product);
            $ShopHistories[] = $New;
        }

        $this->productHistoryRepository->saveCustomerHistory($SaveHistories);

        $ShopHistories = $this->getGuestShopHistory()
            ->merge($this->getCustomerShopHistory(($Customer)));
        $ShopHistories = $ShopHistories->sort(function ($a, $b) {
            return $a->getCreateDate() > $b->getCreateDate() ? -1 : 1;
        })->unique(function ($item) {
            if (!$item->getShop()) {
                return null;
            }
            return $item->getShop()->getId();
        }, function ($a, $b) {
            return $a->getCreateDate() > $b->getCreateDate() ? 1 : -1;
        });
        $SaveHistories = [];
        foreach($ShopHistories as $ShopHistory) {
            if (empty($ShopHistory->getShop()->getId())) {
                continue;
            }
            $Shop = $this->entityManager->getRepository('Customize\Entity\Shop')
                ->find($ShopHistory->getShop()->getId());
            $New = new ShopCustomerHistory();
            $New->setCustomer($Customer);
            $New->setShop($Shop);
            $New->setCreateDate($ShopHistory->getCreateDate());
            $SaveHistories[] = $New;
        }
        $ShopHistories = $ShopHistories->map(function ($ShopHistory) use ($Customer) {
            $New = new ShopCustomerHistory();
            $New->setCustomer($Customer);
            $New->setShop($ShopHistory->getShop());
            $New->setCreateDate($ShopHistory->getCreateDate());
            return $New;
        });
        $this->shopHistoryRepository->saveCustomerHistory($ShopHistories);
    }

    /**
     * @return Collection
     */
    protected function getGuestProductHistory()
    {
        return $this->session->get(self::SESSION_KEY_PRODUCT_HISTORY, new Collection());
    }

    /**
     * @param Customer $Customer
     * @return Collection
     */
    protected function getCustomerProductHistory(Customer $Customer)
    {
        return Collection::from($this->productHistoryRepository->getCustomerHistory($Customer));
    }

    /**
     * @param Shop $Shop
     */
    protected function saveGuestShopHistory(Shop $Shop)
    {
        $Histories = $this->getGuestShopHistory();
        $this->ensureMaxLimitAndRemoveDuplicatesFromHistories(
            $Histories,
            function ($His) use ($Shop) {
                /** @var ShopCustomerHistory $His */
                return !$His->getShop() || $His->getShop()->getId() == $Shop->getId();
            },
            (!empty($this->maxCount)) ? $this->maxCount - 1 : 0
        );
        $History = new ShopCustomerHistory();
        $History->setShop($Shop);
        $History->setCreateDate(date_create());
        $Histories->add($History);
        $this->session->set(self::SESSION_KEY_SHOP_HISTORY, $Histories);
    }

    /**
     * @param Shop $Shop
     * @param Customer $Customer
     */
    protected function saveCustomerShopHistory(Shop $Shop, Customer $Customer)
    {
        $Histories = $this->getCustomerShopHistory($Customer);
        $DeleteHistories = $this->ensureMaxLimitAndRemoveDuplicatesFromHistories(
            $Histories,
            function ($His) use ($Shop) {
                /** @var ShopCustomerHistory $His */
                return !$His->getShop() || $His->getShop()->getId() == $Shop->getId();
            },
            $this->maxCount
        );
        foreach($DeleteHistories as $Deleted) {
            $this->entityManager->remove($Deleted);
        }
        $History = new ShopCustomerHistory();
        $History->setCustomer($Customer);
        $History->setShop($Shop);
        $History->setCreateDate(date_create());
        $Histories->add($History);
        $this->entityManager->persist($History);
        $this->entityManager->flush();
    }

    /**
     * @param Product $Product
     */
    protected function saveGuestProductHistory(Product $Product)
    {
        $Histories = $this->getGuestProductHistory();

        $this->ensureMaxLimitAndRemoveDuplicatesFromHistories(
            $Histories,
            function ($His) use ($Product) {
                /** @var ProductCustomerHistory $His */
                return !$His->getProduct() || $His->getProduct()->getId() == $Product->getId();
            },
            (!empty($this->maxCount)) ? $this->maxCount - 1 : 0
        );

        $History = new ProductCustomerHistory();
        $History->setProduct($Product);
        $Histories->add($History);
        $this->session->set(self::SESSION_KEY_PRODUCT_HISTORY, $Histories);
    }

    /**
     * @param Product $Product
     * @param Customer $Customer
     */
    protected function saveCustomerProductHistory(Product $Product, Customer $Customer)
    {
        $Histories = $this->getCustomerProductHistory($Customer);
        $DeleteHistories = $this->ensureMaxLimitAndRemoveDuplicatesFromHistories(
            $Histories,
            function ($His) use ($Product) {
                /** @var ProductCustomerHistory $His */
                return !$His->getProduct() || $His->getProduct()->getId() == $Product->getId();
            },
            $this->maxCount
        );
        foreach($DeleteHistories as $DeleteHistory) {
            $this->entityManager->remove($DeleteHistory);
        }
        $History = new ProductCustomerHistory();
        $History->setProduct($Product);
        $History->setCustomer($Customer);
        $Histories->add($History);
        $this->entityManager->persist($History);
        $this->entityManager->flush();
    }

    /**
     * @param Collection $Histories
     * @param \Closure $duplicateFinder
     * @param integer|null $max
     * @return array
     */
    protected function ensureMaxLimitAndRemoveDuplicatesFromHistories($Histories, $duplicateFinder, $max = null)
    {
        $DeleteHistories = [];
        /** @var ShopCustomerHistory $History */
        foreach($Histories as $History) {
            if ($duplicateFinder($History)) {
                $DeleteHistories[] = $History;
                $Histories->removeElement($History);
            }
        }
        if (!(empty($max))) {
            while ($Histories->count() > $max && $Histories->count() > 0) {
                $First = $Histories->first();
                if ($First) {
                    $DeleteHistories[] = $First;
                    $Histories->removeElement($First);
                }
            }
        }
        return $DeleteHistories;
    }

    /**
     * @return null|Customer
     */
    protected function getCustomer()
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return null;
        }
        $Customer = $token->getUser();
        if (!$Customer instanceof Customer) {
            return null;
        }
        return $Customer;
    }
}
