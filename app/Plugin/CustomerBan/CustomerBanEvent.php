<?php

namespace Plugin\CustomerBan;

use Doctrine\ORM\QueryBuilder;
use Eccube\Application;
use Eccube\Entity\Customer;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Eccube\Service\CartService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\SecurityEvents;
use Twig\Environment as Twig;

class CustomerBanEvent implements EventSubscriberInterface
{
    /** @var Twig */
    protected $twig;

    /** @var Session */
    protected $session;

    /** @var TokenStorageInterface */
    protected $token;

    /** @var CartService */
    protected $cart;

    public function __construct(
        Twig $twig,
        Session $session,
        TokenStorageInterface $token,
        CartService $cartService
    )
    {
        $this->twig = $twig;
        $this->session = $session;
        $this->token = $token;
        $this->cart = $cartService;
    }

    public static function getSubscribedEvents()
    {
        return [
            '@admin/Customer/index.twig' => 'onRenderAdminCustomerIndex',
            EccubeEvents::ADMIN_CUSTOMER_INDEX_SEARCH => 'onAdminCustomerIndexSearch',
            'Mypage/index.twig' => 'checkBanOnRender',
            'Mypage/history.twig' => 'checkBanOnRender',
            'Mypage/favorite.twig' => 'checkBanOnRender',
            'Mypage/change.twig' => 'checkBanOnRender',
            'Mypage/delivery.twig' => 'checkBanOnRender',
            'Product/list.twig' => 'checkBanOnRender',
            'Product/detail.twig' => 'checkBanOnRender',
            'Shopping/index.twig' => 'checkBanOnRender',
            'Shopping/confirm.twig' => 'checkBanOnRender',
            'Cart/index.twig' => 'checkBanOnRender',
        ];
    }

    public function onRenderAdminCustomerIndex(TemplateEvent $event)
    {
        $source = $this->twig->getLoader()
            ->getSourceContext('@CustomerBan/admin/customer_index.twig')
            ->getCode();
        $event->setSource($source);
    }

    public function onAdminCustomerIndexSearch(EventArgs $event)
    {
        /** @var QueryBuilder $qb */
        $qb = $event->getArgument('qb');
        $qb->addSelect('cb')
            ->leftJoin('c.CustomerBan', 'cb');
    }

    public function checkBanOnRender(TemplateEvent $event)
    {
        if (!$this->isBanned()) {
            return;
        }
        $this->cart->clear();
        $this->cart->save();
        $event->addSnippet('CustomerBan/Resource/template/ban_alert.twig');
        $this->addRequestError('customer_ban.front.ban_alert');
    }

    protected function isBanned()
    {
        $Customer = $this->token->getToken()->getUser();
        if (!$Customer instanceof Customer) {
            return false;
        }
        return $Customer->isBanned();
    }

    public function addRequestError($message, $namespace = 'front')
    {
        $this->session->getFlashBag()->set('eccube.'.$namespace.'.request.error', $message);
    }

    public function clearMessage()
    {
        $this->session->getFlashBag()->clear();
    }
}
