<?php

namespace App\Controller;

use App\Entity\Catering;
use App\Form\CateringType;
use App\Repository\CateringRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/catering')]
final class CateringAdminController extends AbstractController{
    #[Route(name: 'app_admin_catering_index', methods: ['GET'])]
    public function index(CateringRepository $cateringRepository): Response
    {
        return $this->render('catering/indexAdmin.html.twig', [
            'caterings' => $cateringRepository->findAll(),
        ]);
    }

    #[Route('/{CateringId}', name: 'app_admin_catering_show', methods: ['GET'])]
    public function show(Catering $catering): Response
    {
        return $this->render('catering/showAdmin.html.twig', [
            'catering' => $catering,
        ]);
    }

    #[Route('/{CateringId}', name: 'app_admin_catering_delete', methods: ['POST'])]
    public function delete(Request $request, Catering $catering, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$catering->getCateringId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($catering);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_catering_index', [], Response::HTTP_SEE_OTHER);
    }
}
