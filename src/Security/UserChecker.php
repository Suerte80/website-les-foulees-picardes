<?php

namespace App\Security;

use App\Entity\Member;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if(!$user instanceof Member) {
            return;
        }

        if(!$user->isActive()) {
            throw new CustomUserMessageAuthenticationException('Votre compte n\'est pas actif.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {

    }
}
