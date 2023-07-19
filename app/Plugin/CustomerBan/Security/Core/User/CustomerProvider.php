<?php

namespace Plugin\CustomerBan\Security\Core\User;

use Eccube\Entity\Master\CustomerStatus;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class CustomerProvider extends \Eccube\Security\Core\User\CustomerProvider
{
    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        $Customer = $this->customerRepository->findOneBy([
            'email' => $username,
            'Status' => CustomerStatus::REGULAR,
        ]);

        if (null === $Customer) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        if ($Customer instanceof \Eccube\Entity\Customer && $Customer->getCustomerBan()) {
            throw new CustomUserMessageAuthenticationException('Your account has been banned.');
        }

        return $Customer;
    }
}
