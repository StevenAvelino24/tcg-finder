<?php

namespace App\Service\User;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final class UserService implements UserServiceInterface
{
    public function __construct(
        protected readonly VerifyEmailHelperInterface $verifyEmailHelper,
        protected readonly MailerInterface $mailer,
    ) {}

    public function sendVerificationEmail(string $userId, string $userEmail): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            'auth_verify_email',
            $userId,
            $userEmail,
            ['id' => $userId]
        );

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@tcg-finder.ch', 'TCG Finder'))
            ->to((string) $userEmail)
            ->subject('Please confirm your email')
            ->htmlTemplate('emails/verify_email.html.twig')
            ->context([
                'userEmail' => $userEmail,
                'signedUrl' => $signatureComponents->getSignedUrl(),
            ]);

        $this->mailer->send($email);
    }
}