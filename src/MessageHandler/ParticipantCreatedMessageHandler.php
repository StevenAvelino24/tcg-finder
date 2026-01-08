<?php

namespace App\MessageHandler;

use App\Message\ParticipantCreatedMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler()]
final class ParticipantCreatedMessageHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private TranslatorInterface $translator
    ) {}

    public function __invoke(ParticipantCreatedMessage $message): void
    {
        $email = (new Email())
            ->from('no-reply@tcg-finder.ch')
            ->to($message->email)
            ->subject($this->translator->trans('email.new_participant.subject'))
            ->text($this->translator->trans('email.new_participant.message', [
                'firstName' => $message->firstName,
                'lastName' => $message->lastName,
                'email' => $message->email,
                'token' => $message->token
            ]));

        $this->mailer->send($email);
    }
}