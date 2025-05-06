<?php

namespace App\Controller;

use App\Repository\VenueRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AutocompleteController
{
    #[Route('/autocomplete/venues', name: 'autocomplete_venues')]
    public function autocomplete(Request $request, VenueRepository $venueRepository): JsonResponse
    {
        $term = $request->query->get('term');

        $results = $venueRepository->createQueryBuilder('v')
            ->where('v.VenueName LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $data = array_map(fn($venue) => [
            'id' => $venue->getId(),
            'text' => $venue->getVenueName()
        ], $results);

        return new JsonResponse($data);
    }
}
