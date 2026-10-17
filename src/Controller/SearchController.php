<?php

namespace App\Controller;

use App\Service\Search\SearchServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'search_')]
final class SearchController extends AbstractController
{
    #[Route('/search', name: 'base', methods: ['GET'])]
    public function search(
        Request $request,
        SearchServiceInterface $searchService
    ): JsonResponse {
        $filters = $request->query->all('filters');
        $limit = $request->query->getInt('limit', 20);
        $index = $request->query->get('index');
        $geoBox = $request->query->all('geoBox');

        return $this->json($searchService->search($filters, $index, $geoBox, $limit), status: Response::HTTP_OK);
    }
}