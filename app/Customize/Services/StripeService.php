<?php

namespace Customize\Services;

require_once \dirname(__FILE__) . '/../vendor/stripe-php/init.php';

use Stripe;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Customize\Entity\StripeConfig;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService {
    
    protected $container;
    protected $entityManager;
    protected $router;


    public function __construct(
        ContainerInterface $container,
        RouterInterface $router
    )
    {
        $this->container = $container;
        $this->entityManager = $container->get('doctrine.orm.entity_manager');

        $stripe_config = $this->entityManager->getRepository(StripeConfig::class)->get();

        if ($stripe_config) {
            Stripe\Stripe::setApiKey($stripe_config->getSecretKey());
        }
        $this->router = $router;
    }
    public function createAccount($temp_data = null) {

        $account_data = [
            'country'   =>  'JP',
            'type'      =>  'express'
        ];
        if ($temp_data) {
            $account_data['email'] = $temp_data->getOrderMail();
        }
        $account = Stripe\Account::create($account_data);
        return $account;
    }
    
    public function createAccountLink($account) {
        
        if(\is_string($account)) {
            $account_id = $account;
        } else {
            $account_id = $account->id;
        }

        $account_links = Stripe\AccountLink::create([
            'account'       =>  $account_id,
            'refresh_url'   =>  $this->router->generate("malldevel_shop_register_refresh", [], UrlGeneratorInterface::ABSOLUTE_URL),
            'return_url'    =>  $this->router->generate("malldevel_applicant_stripe_apply", [], UrlGeneratorInterface::ABSOLUTE_URL),
            'type'          =>  'account_onboarding',
        ]);
        return $account_links;
    }

    public function retrieveAccount($account_id)
    {
        return Stripe\Account::retrieve($account_id);
    }

    public function acceptTos($account)
    {
        if (!empty($account->tos_acceptance->date)) {
            return $account;
        }
        $account = Stripe\Account::update($account->id, 
            [
                'tos_acceptance' => [
                    'date' => time(),
                    'ip' => '203.173.114.77', // Assumes you're not using a proxy
                  ],
            ]);
        return $account;
    }

    public function uploadFile($account_id, $file_name)
    {
        $stripe_file = Stripe\File::create([
            'purpose'   =>  'identity_document',
            'file'      =>  \fopen($file_name, 'r'),
        ], [
            'stripe_account'    =>  $account_id
        ]);

        $account = Stripe\Account::updatePerson(
            $account_id,
            "person_IysehclMXSet9c",
            [
                'verification'  =>  [
                    'document'  =>  [
                        'front' =>  $stripe_file->id
                    ]
                ]
            ]
        );
        
        return $account;
    }
}