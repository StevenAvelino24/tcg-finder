<?php

namespace App\Security\Voter;

use App\Entity\Shop;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ShopVoter extends Voter
{
    public const EDIT = 'SHOP_EDIT';
    public const DELETE = 'SHOP_DELETE';
    public const BACKEND_SHOW = 'SHOP_BACKEND_SHOW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE, self::BACKEND_SHOW], true)
            && $subject instanceof Shop;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return $user === $subject->getUser()
            || in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}
