<?php

namespace Customize\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Eccube\Repository\MailTemplateRepository;
use Eccube\Repository\MailHistoryRepository;
use Eccube\Repository\BaseInfoRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Eccube\Common\EccubeConfig;
use Customize\Entity\Shop;
use Customize\Entity\Apply;


class MailService extends \Eccube\Service\MailService {

    const SHOP_CREATED = "Shop created";
    const APPLICANT_CREATED = "Applicant created";
    const APPLICANT_HOLDED = "Applicant Holded";
    const APPLICATION_REGISTERED = "Application registered";
    
    protected $container;
    protected $rec_order_repo;
    protected $entityManager;

    protected $error;


    public function __construct(
        ContainerInterface $container,
        \Swift_Mailer $mailer,
        MailTemplateRepository $mailTemplateRepository,
        MailHistoryRepository $mailHistoryRepository,
        BaseInfoRepository $baseInfoRepository,
        EventDispatcherInterface $eventDispatcher,
        \Twig_Environment $twig,
        EccubeConfig $eccubeConfig
    ) {
        $this->container = $container;
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        
        parent::__construct($mailer, $mailTemplateRepository, $mailHistoryRepository, $baseInfoRepository, $eventDispatcher, $twig, $eccubeConfig);
    }

    public function getError()
    {
        return $this->error;
    }
    public function sendShopCreatedMailToShopOwner(Apply $Apply, Shop $Shop = null)
    {
        if (!$Shop) {
            $Shop = $this->entityManager->getRepository(Shop::class)->findOneBy(['apply_id' => $Apply->getId()]);
            if (!$Shop) {
                $this->error = "Shop is not created for temp owner_id {$Apply->getId()}";
                return false;
            }
        }
        $this->sendMail($Apply->getOrderMail(), self::SHOP_CREATED, compact('Apply', 'Shop'));
        log_info("Mail was sent\n\tmail type: SHOP_CREATED,  mail: {$Apply->getOrderMail()}");
        return true;
    }

    public function sendApplicationRegisteredMail(Apply $Apply) {
        $this->sendMail($Apply->getOrderMail(), self::APPLICATION_REGISTERED, compact('Apply'));
        log_info("Mail was sent\n\tmail type: APPLICATION_REGISTERED,  mail: {$Apply->getOrderMail()}");
    }

    public function sendApplicantCreatedMail(Apply $Apply) 
    {
        $this->sendMail($Apply->getOrderMail(), self::APPLICANT_CREATED, compact('Apply'));
        log_info("Mail was sent\n\tmail type: APPLICANT_CREATED,  mail: {$Apply->getOrderMail()}");
    }
    public function sendApplicantHoldMail(Apply $Apply)
    {
        $this->sendMail($Apply->getOrderMail(), self::APPLICANT_HOLDED, compact('Apply'));
        log_info("Mail was sent\n\tmail type: APPLICANT_CREATED,  mail: {$Apply->getOrderMail()}");
    }

    public function sendMail($to, $template_name, $params)
    {
        $template = $this->mailTemplateRepository->findOneBy([
            'name'  =>  $template_name
        ]);

        $template_path = $template->getFileName();
        $engine = $this->container->get('twig');
        $body = $engine->render($template_path, $params, null);
        $htmlFileName = $this->getHtmlTemplate($template_path);

        $message = $this->initializeMail($to, $template);
        $message->setBody($body);

        if ($htmlFileName) {
            $htmlBody = $this->twig->render($htmlFileName, $params);
            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        }
        $this->mailer->send($message);
    }

    protected function initializeMail($to, $template) 
    {
        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$template->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$to])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        return $message;
    }
}