<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly array $supportedLocales = ['en', 'fr', 'de', 'it'])
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest()) {
            return;
        }

        $locale = $request->attributes->get('_locale');

        if (!$locale && $request->hasPreviousSession()) {
            $locale = $request->getSession()->get('_locale');
        }

        if (!$locale) {
            $locale = $request->getPreferredLanguage($this->supportedLocales);
        }

        $request->setLocale($locale ?? 'fr');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}