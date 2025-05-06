<?php

namespace App\Controller;

use App\Entity\Venue;
use App\Form\VenueType;
use App\Repository\VenueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpClient\HttpClient;

#[Route('/venue')]
final class VenueController extends AbstractController{
    #[Route(name: 'app_venue_index', methods: ['GET'])]
    public function index(Request $request, VenueRepository $venueRepository, PaginatorInterface $paginator): Response
    {

        // Get filters and sort parameters from the request
        $location = $request->query->get('location');
        $availability = $request->query->get('availability');
        $parking = $request->query->get('parking');
        $venueName = $request->query->get('venue_name');
        
        $sortBy = $request->query->get('sort_by', 'VenueId');
        $sortDirection = $request->query->get('sort_dir', 'ASC');
        

        $allowedSortFields = ['VenueId', 'VenueName', 'Location', 'Availability', 'Parking', 'NbrPlaces', 'Cost'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'VenueId'; // fallback to safe default
        }
        $venues = $venueRepository->findFilteredVenues($location, $availability, $parking, $venueName);
        $queryBuilder = $venueRepository->getFilteredQueryBuilder(
            $location, 
            $availability, 
            $parking, 
            $venueName, 
            $sortBy, 
            $sortDirection
        );
        // Paginate the QueryBuilder
        $venues = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            5, // Items per page
            [
                'defaultSortFieldName' => 'v.'.$sortBy,
                'defaultSortDirection' => $sortDirection,
                'pageParameterName' => 'page',
                'sortFieldParameterName' => 'sort_by',
                'sortDirectionParameterName' => 'sort_dir'
            ]
        );

        $bestVenue = $venueRepository->findBestQualityPriceVenue();

        return $this->render('venue/index.html.twig', [
            'venues' => $venues,
            'best_venue' => $bestVenue,
            'sort_by' => $sortBy, 
            'sort_dir' => $sortDirection,
            'current_filters' => [
                'location' => $location,
                'availability' => $availability,
                'parking' => $parking,
                'venue_name' => $venueName,
            ],
        ]);
    }

    #[Route('/new', name: 'app_venue_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $venue = new Venue();
        $form = $this->createForm(VenueType::class, $venue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $location = $venue->getVenueName() . ', ' . $venue->getLocation();
            if ($location && !$venue->getLatitude()) {
                $client = HttpClient::create();
                $response = $client->request('GET', 'https://nominatim.openstreetmap.org/search', [
                    'query' => [
                        'q' => $location,
                        'format' => 'json',
                    ],
                ]);
                $data = $response->toArray();
                if ($data) {
                    $venue->setLatitude($data[0]['lat']);
                    $venue->setLongitude($data[0]['lon']);
                }
            }
            $entityManager->persist($venue);
            $entityManager->flush();

            return $this->redirectToRoute('app_venue_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('venue/new.html.twig', [
            'venue' => $venue,
            'form' => $form,
        ]);
    }

    #[Route('/{VenueId}', name: 'app_venue_show', methods: ['GET'])]
    public function show(Venue $venue, VenueRepository $venueRepository): Response
    {
        $recommendations = $venueRepository->findRecommendations($venue);
        return $this->render('venue/show.html.twig', [
            'venue' => $venue,
            'recommendations' => $recommendations,
        ]);
    }

    #[Route('/{VenueId}/edit', name: 'app_venue_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Venue $venue, EntityManagerInterface $entityManager): Response
    {
        // Store original values before handling the request
        $originalData = [
            'location' => $venue->getLocation(),
            'venueName' => $venue->getVenueName()
        ];

        $form = $this->createForm(VenueType::class, $venue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if location or name was actually changed
            $locationChanged = $originalData['location'] !== $venue->getLocation();
            $nameChanged = $originalData['venueName'] !== $venue->getVenueName();

            if ($locationChanged || $nameChanged) {
                $searchTerms = [
                    $venue->getVenueName() . ', ' . $venue->getLocation(),
                    $venue->getLocation()
                ];
                
                // Rest of your geocoding logic...
            }

            $entityManager->flush();
            return $this->redirectToRoute('app_venue_index');
        }

        return $this->render('venue/edit.html.twig', [
            'venue' => $venue,
            'form' => $form,
        ]);
    }

    #[Route('/{VenueId}', name: 'app_venue_delete', methods: ['POST'])]
    public function delete(Request $request, Venue $venue, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$venue->getVenueId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($venue);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_venue_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/venue/map', name: 'app_venue_map')]
    public function map(VenueRepository $venueRepository): Response
    {
        $venues = $venueRepository->findAll();

        $venueData = array_map(function ($venue) {
            return [
                'id' => $venue->getVenueId(),
                'name' => $venue->getVenueName(),
                'lat' => $venue->getLatitude(),
                'lng' => $venue->getLongitude(),
                'nbrPlaces' => $venue->getNbrPlaces(),
                'cost' => $venue->getCost(),
                'parking' => $venue->getParking(),
                'availability' => $venue->getAvailability()
            ];
        }, $venues);

        return $this->render('venue/map.html.twig', [
            'venue_map_data' => $venueData
        ]);
    }

}
