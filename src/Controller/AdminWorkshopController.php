<?php

namespace App\Controller;

use App\Entity\Workshop;
use App\Form\WorkshopType;
use App\Repository\WorkshopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/workshop')]
final class AdminWorkshopController extends AbstractController
{
    #[Route(name: 'app_admin_workshop_index', methods: ['GET'])]
    public function index(WorkshopRepository $workshopRepository): Response
    {
        return $this->render('workshop/indexBack.html.twig', [
            'workshops' => $workshopRepository->findAll(),
        ]);
    }

    #[Route('/{workshopId}', name: 'app_admin_workshop_show', methods: ['GET'])]
    public function show(Workshop $workshop): Response
    {
        return $this->render('workshop/showBack.html.twig', [
            'workshop' => $workshop,
        ]);
    }
}
