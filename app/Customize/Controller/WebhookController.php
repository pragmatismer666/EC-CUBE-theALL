<?php

namespace Customize\Controller;

if( \file_exists(dirname(__FILE__).'/../vendor/stripe-php/init.php')) {
    include_once(dirname(__FILE__).'/../vendor/stripe-php/init.php');
}

use Stripe\Webhook;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Customize\Entity\StripeConfig;

class WebhookController extends AbstractController
{
    protected $container;
    protected $entityManager;

    private $stripe_config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->entityManager = $this->container->get('doctrine.orm.entity_manager');
        $this->stripe_config = $this->entityManager->getRepository(StripeConfig::class)->get();
    }

    /**
     * @Route("/stripe/webhook", name="malldevel_stripe_webhook")
     */
    public function webhook(Request $request)
    {

        if (!$this->stripe_config) {
            throw new NotFoundHttpException();
        }
        $signature = $this->stripe_config->getSignature();
        try {
            log_info("============[webhook sign started]===========\n");
            $event = Webhook::constructEvent(
                $request->getContent(), 
                $request->headers->get('stripe-signature'),
                $signature, 800
            );
            
            $type = $event['type'];
            $object = $event['data']['object'];
        } catch(Exception $e) {
            log_error("============[webhook sign error]========\n");
            return $this->json(['status'    =>  'failed'], );
        }

        log_info("webhook type: $type\n");

        switch ($type) {
            case "account.updated":
                log_info('🔔 ' . $type . ' Webhook received! ' . $object);
            case 'capabiliity.updated':
                log_info('🔔 ' . $type . ' Webhook received! ' . $object);
            case 'person.created':
                log_info('🔔 ' . $type . ' Webhook received! ' . $object);     
            case 'account.external_account.created':
                log_info('🔔 ' . $type . ' Webhook received! ' . $object);     
            break;
            default:
        }
        return $this->json(['status'    =>  'success']);
        
    }
}
