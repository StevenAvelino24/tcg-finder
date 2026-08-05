<?php

namespace App\Service\Geocoding;

interface GeocodingServiceInterface
{
    public function getLatAndLonFromAddress(string $address, string $zipcode, string $city): array;
}