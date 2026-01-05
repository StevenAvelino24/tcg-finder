<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\Shop;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EventVoter extends Voter
{
    public const CREATE = 'EVENT_CREATE';
    public const EDIT = 'EVENT_EDIT';
    public const DELETE = 'EVENT_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::EDIT, self::DELETE], true)
            && $subject instanceof Event;
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

        $shop = $user->getShop();

        if (!$shop instanceof Shop) {
            return false;
        }

        if (!$shop->getEnabled()) {
            return false;
        }

        return $user === $subject->getShop()->getUser()
            || in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}
