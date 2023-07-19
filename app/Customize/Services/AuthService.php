<?php


namespace Customize\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Eccube\Common\EccubeConfig;
use Customize\Entity\Shop;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Eccube\Entity\Member;
class AuthService {
    protected $container;
    protected $entityManager;
    protected $eccubeConfig;
    protected $context;
    protected $tokenStorage;

    public function __construct(
        ContainerInterface $container,
        EccubeConfig $eccubeConfig,
        TokenStorageInterface $tokenStorage
    ) {
        $this->container = $container;
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->eccubeConfig = $eccubeConfig;
        
        $this->tokenStorage = $tokenStorage;
        // if (\is_object($token->getUser())) {
        //     // e.g. anonymous authentication
        //     $this->user = $token->getUser();
        // }
    }

    public function getCurrentRole() {
        $token = $this->tokenStorage->getToken();
        if (\is_object($token->getUser())) {
            // e.g. anonymous authentication
            $user = $token->getUser();
            if ( !$user ) {
                return null;
            }
            if ( $user instanceof Member ) {
                return $user->getRole();
            }
        }
        return "ROLE_CUSTOMER";
    }
    // get current admin member
    public function getCurrentMember() 
    {
        $token = $this->tokenStorage->getToken();
        if (\is_object($token->getUser())) {
            // e.g. anonymous authentication
            $user = $token->getUser();
            if ( !$user ) {
                return null;
            }
            if ( $user instanceof Member ) {
                return $user;
            }
        }
        return $user;
    }
}