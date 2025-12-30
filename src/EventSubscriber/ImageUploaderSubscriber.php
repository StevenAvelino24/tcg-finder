<?php

namespace App\EventSubscriber;

use App\Entity\Image;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ImageUploaderSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_UPLOAD => 'onPostUpload',
        ];
    }

    public function onPostUpload(Event $event): void
    {
        /** @var Image */
        $object = $event->getObject();

        if (!$object instanceof Image) {
            return;
        }

        $mapping = $event->getMapping();

        $path = $mapping->getUploadDestination() . '/' . $object->getFileName();

        if (!file_exists($path)) {
            return;
        }

        $this->convertToWebp($path, $object);
    }

    private function convertToWebp(string $path, Image $image): void
    {
        $imageResource = imagecreatefromstring(file_get_contents($path));
        if (!$imageResource) {
            return;
        }

        $webpPath = preg_replace('/\.\w+$/', '.webp', $path);
        $webpName = basename($webpPath);

        imagewebp($imageResource, $webpPath, 90);
        unset($imageResource);

        unlink($path);

        $image->setFileName($webpName);
    }
}