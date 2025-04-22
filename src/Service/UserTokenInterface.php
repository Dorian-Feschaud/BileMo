<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserTokenInterface {

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    )
    {
    }
    
    public function getCurrentUser():?UserInterface
    {
        $token = $this->tokenStorage->getToken();
        if ($token instanceof TokenInterface) {
            $user = $token->getUser();
            return $user;
        }
        else {
            return null;
        }
    }

    public function getCustomerId(): ?int
    {
        $currentUser = $this->getCurrentUser();
        if ($currentUser instanceof User) {
            if (in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
                return null;
            }
            else {
                return $currentUser->getCustomer()->getId();
            }
                
        }
        else {
            return null;
        }
    }
}