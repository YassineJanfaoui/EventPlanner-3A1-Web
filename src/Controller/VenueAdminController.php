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

#[Route('/admin/venue')]
final class VenueAdminController extends AbstractController{
    #[Route(name: 'app_admin_venue_index', methods: ['GET'])]
    public function index(VenueRepository $venueRepository): Response
    {
        return $this->render('venue/indexAdmin.html.twig', [
            'venues' => $venueRepository->findAll(),
        ]);
    }

    #[Route('/{VenueId}', name: 'app_admin_venue_show', methods: ['GET'])]
    public function show(Venue $venue): Response
    {
        return $this->render('venue/showAdmin.html.twig', [
            'venue' => $venue,
        ]);
    }

    #[Route('/{VenueId}', name: 'app_admin_venue_delete', methods: ['POST'])]
    public function delete(Request $request, Venue $venue, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$venue->getVenueId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($venue);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_venue_index', [], Response::HTTP_SEE_OTHER);
    }
    
}
