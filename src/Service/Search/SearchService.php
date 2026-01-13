<?php

namespace App\Service\Search;

use Elastic\Elasticsearch\Client;

final class SearchService implements SearchServiceInterface
{
    public function __construct(private Client $client) {}
    
    public function search(array $filters, string $index, array $geoBox, int $limit = 20): array
    {
        if (empty($geoBox)) {
            return [
                'total' => 0,
                'results' => [],
            ];
        }

        $body = [
            'from' => 0,
            'size' => $limit,
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'geo_bounding_box' => [
                                'location' => [
                                    'top_left' => [
                                        'lat' => (float) $geoBox['top_left']['lat'],
                                        'lon' => (float) $geoBox['top_left']['lon'],
                                    ],
                                    'bottom_right' => [
                                        'lat' => (float) $geoBox['bottom_right']['lat'],
                                        'lon' => (float) $geoBox['bottom_right']['lon'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($filters as $field => $value) {
            if (is_array($value) && $this->isNestedFilter($value)) {
                foreach ($value as $nestedField => $nestedValue) {
                    $body['query']['bool']['filter'][] = [
                        'nested' => [
                            'path' => $field,
                            'query' => [
                                is_array($nestedValue) ? 'terms' : 'term' => [$nestedField => $nestedValue]
                            ]
                        ]
                    ];
                }
            } 
            else {
                $body['query']['bool']['filter'][] = [
                    is_array($value) ? 'terms' : 'term' => [$field => $value]
                ];
            }
        }

        $response = $this->client->search([
            'index' => $index,
            'body' => $body,
        ]);

        return [
            'total' => $response['hits']['total']['value'] ?? 0,
            'results' => array_map(
                fn ($hit) => $hit['_source'],
                $response['hits']['hits']
            ),
        ];
    }

    private function isNestedFilter(array $value): bool
    {
        return !empty(array_filter(array_keys($value), fn($subValue) => str_contains($subValue, '.')));
    }
}