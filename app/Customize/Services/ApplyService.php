<?php

namespace Customize\Services;

use Customize\Entity\Apply;
use Customize\Entity\EAuthority;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Eccube\Entity\Master\Authority;
use Eccube\Entity\Master\Work;
use Eccube\Entity\Member;

class ApplyService {
    protected $container;
    protected $entityManager;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * @param Apply $Apply
     * @return Member $Member
     */
    public function createApplicant(Apply $Apply)
    {
        $memberRepository = $this->entityManager->getRepository(Member::class);
        $Member = $memberRepository->findOneBy(['apply_id' => $Apply->getId()]);
        if ($Member) return $Member;

        $Member = new Member;
        $Member->copyFromApply($Apply);
        $Member->setCreator($this->getUser());

        $Work = $this->entityManager->getRepository(Work::class)->find(Work::ACTIVE);
        $Member->setWork($Work);

        $Authority = $this->entityManager->getRepository(Authority::class)->find(EAuthority::SHOP_APPLICANT);
        $Member->setAuthority($Authority);
        
        $encoder_factory = $this->container->get('security.encoder_factory');
        $encoder = $encoder_factory->getEncoder($Member);

        $salt = $encoder->createSalt();

        // randomly generate a password
        $password = $this->random_password(10);
        $Apply->setPassword($password);
        $this->entityManager->persist($Apply);

        $encoded_password = $encoder->encodePassword($password, $salt);

        $Member
            ->setSalt($salt)
            ->setPassword($encoded_password);
        
        $this->entityManager->getRepository(Member::class)->save($Member);
        $this->entityManager->flush();

        $mailService = $this->container->get("malldevel.email.service");
        $mailService->sendApplicantCreatedMail($Apply);
    }

    public function random_password($length){
        //A list of characters that can be used in our
        //random password.
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!-.[]?*()';
        //Create a blank string.
        $password = '';
        //Get the index of the last character in our $characters string.
        $characterListLength = mb_strlen($characters, '8bit') - 1;
        //Loop from 1 to the $length that was specified.
        foreach(range(1, $length) as $i){
            $password .= $characters[random_int(0, $characterListLength)];
        }
        return $password;
    }
    protected function getUser()
    {
        if (!$this->container->has('security.token_storage')) {
            return null;
        }

        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return null;
        }

        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }
}