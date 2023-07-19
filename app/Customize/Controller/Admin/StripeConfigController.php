<?php

namespace Customize\Controller\Admin;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Customize\Repository\StripeConfigRepository;
use Customize\Entity\StripeConfig;
use Customize\Form\Type\Admin\StripeConfigType;

class StripeConfigController extends AbstractController {

    protected $container;
    protected $stripeConfigRepository;

    public function __construct(
        ContainerInterface $container,
        StripeConfigRepository $stripeConfigRepository
    ){
        $this->container = $container;
        $this->stripeConfigRepository = $stripeConfigRepository;
    }
    
    /**
     * @Route("/%eccube_admin_route%/stripe/config", name="malldevel_admin_stripe_config")
     * @Template("@admin/Stripe/config.twig")
     */
    public function index(Request $request) 
    {
        $stripeConfig = $this->stripeConfigRepository->get();
        if(!$stripeConfig) {
            $stripeConfig = new StripeConfig;
        }
        $form = $this->createForm(StripeConfigType::class, $stripeConfig);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $stripeConfig = $form->getData();
            $this->entityManager->persist($stripeConfig);
            $this->entityManager->flush();
            $this->addSuccess('malldevel.admin.stripe.config.save_success', 'admin');
        }
        return [
            'form'  =>  $form->createView()
        ];
    }
}
