<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Image;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ImageVoter extends Voter
{
    public const DELETE = 'IMAGE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DELETE], true)
            && $subject instanceof Image;
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

        return $user === $subject->getShop()->getUser()
            || in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}
