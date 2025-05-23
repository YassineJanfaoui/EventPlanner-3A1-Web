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

#[Route('/catering')]
final class CateringController extends AbstractController{
    #[Route(name: 'app_catering_index', methods: ['GET'])]
    public function index(Request $request, CateringRepository $cateringRepository): Response
    {
        $venueNameFilter = $request->query->get('venueName'); // Get the venue name search query
        $menuTypeFilter = $request->query->get('MenuType');
        $mealScheduleFilter = $request->query->get('MealSchedule');
        $sortPrice = $request->query->get('sortPrice'); // Either 'asc' or 'desc'

        $queryBuilder = $cateringRepository->createQueryBuilder('c')
                                        ->leftJoin('c.venue', 'v'); // Join the Venue entity

        // Apply Venue Name search
        if ($venueNameFilter) {
            $queryBuilder->andWhere('v.VenueName LIKE :venueName')
                        ->setParameter('venueName', '%' . $venueNameFilter . '%');
        }

        // Apply filtering for MenuType and MealSchedule
        if ($menuTypeFilter) {
            $queryBuilder->andWhere('c.MenuType = :menuType')
                        ->setParameter('menuType', $menuTypeFilter);
        }
        if ($mealScheduleFilter) {
            $queryBuilder->andWhere('c.MealSchedule = :mealSchedule')
                        ->setParameter('mealSchedule', $mealScheduleFilter);
        }

        // Apply sorting
        if ($sortPrice) {
            $queryBuilder->orderBy('c.Pricing', $sortPrice); // 'asc' or 'desc'
        }

        $caterings = $queryBuilder->getQuery()->getResult();

        return $this->render('catering/index.html.twig', [
            'caterings' => $caterings,
            'venueNameFilter' => $venueNameFilter,
            'menuTypeFilter' => $menuTypeFilter,
            'mealScheduleFilter' => $mealScheduleFilter,
            'sortPrice' => $sortPrice
        ]);
    }

    #[Route('/new', name: 'app_catering_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $catering = new Catering();
        $form = $this->createForm(CateringType::class, $catering);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($catering);
            $entityManager->flush();

            return $this->redirectToRoute('app_catering_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('catering/new.html.twig', [
            'catering' => $catering,
            'form' => $form,
        ]);
    }

    #[Route('/{CateringId}', name: 'app_catering_show', methods: ['GET'])]
    public function show(Catering $catering): Response
    {
        return $this->render('catering/show.html.twig', [
            'catering' => $catering,
        ]);
    }

    #[Route('/{CateringId}/edit', name: 'app_catering_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Catering $catering, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CateringType::class, $catering);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_catering_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('catering/edit.html.twig', [
            'catering' => $catering,
            'form' => $form,
        ]);
    }

    #[Route('/{CateringId}', name: 'app_catering_delete', methods: ['POST'])]
    public function delete(Request $request, Catering $catering, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$catering->getCateringId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($catering);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_catering_index', [], Response::HTTP_SEE_OTHER);
    }
}
