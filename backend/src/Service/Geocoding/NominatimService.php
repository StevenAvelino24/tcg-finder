<?php

namespace App\Service\Geocoding;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class NominatimService implements GeocodingServiceInterface
{

    public function __construct(private CacheInterface $cache) {}

    public function getLatAndLonFromAddress(string $address, string $zipcode, string $city): array
    {
        $lowerAddress = strtolower($address);
        $cacheKey = 'geo_' . md5($lowerAddress);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($address, $zipcode, $city) {
            $item->expiresAfter(60 * 60 * 24 * 30);

            return $this->callNominatim($address, $zipcode, $city);
        });
    }

    private function callNominatim(string $address, string $zipcode, string $city): array
    {
        $query = sprintf(
            '%s, %s %s, %s',
            $address,
            $zipcode,
            $city,
            'Switzerland'
        );

        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
            'q' => $query,
            'format' => 'json',
            'limit' => 1,
        ]);

        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: tcg-finder/1.0\r\n"
            ]
        ]);

        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        if (empty($data)) {
            return [];
        }

        return [
            'lat' => $data[0]['lat'],
            'lon' => $data[0]['lon'],
        ];
    }
}