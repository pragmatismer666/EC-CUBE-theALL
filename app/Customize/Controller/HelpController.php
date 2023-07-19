<?php

namespace Customize\Controller;

use Eccube\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class HelpController extends AbstractController
{
    /** @var  ContainerInterface */
    protected $container;

    public function __construct(
        ContainerInterface $container
    )
    {
        $this->container = $container;
    }

    /**
     * @Route("/help/company", name="malldevel_front_company")
     * @Template("Help/company.twig")
     * @return array
     */
    public function company()
    {
        return [];
    }

    /**
     * @Route("/help/dealer", name="malldevel_front_dealer")
     * @Template("Help/dealer.twig")
     * @return array
     */
    public function dealer()
    {
        return [];
    }

    /**
     * @Route("/help/terms", name="malldevel_front_terms")
     * @Template("Help/terms.twig")
     * @return array
     */
    public function terms()
    {
        return [];
    }

    /**
     * @Route("/help/id-term", name="malldevel_front_id_term")
     * @Template("Help/id-term.twig")
     * @return array
     */
    public function idTerm()
    {
        return [];
    }

    /**
     * @Route("/help/tokusho", name="malldevel_front_tokusho")
     * @Template("Help/tokusho.twig")
     * @return array
     */
    public function tokusho()
    {
        return [];
    }

    /**
     * @Route("/help/contact", name="malldevel_front_contact")
     * @Template("Help/contact.twig")
     * @return array
     */
    public function contact()
    {
        return [];
    }

    /**
     * @Route("/help/privacy", name="malldevel_front_privacy")
     * @Template("Help/privacy.twig")
     * @return array
     */
    public function privacy()
    {
        return [];
    }
}