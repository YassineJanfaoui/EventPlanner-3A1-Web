<?php

namespace App\Controller;

use App\Entity\Bill;
use App\Form\BillType;
use App\Repository\BillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\BillService;


#[Route('/bill')]
final class BillController extends AbstractController
{
    #[Route('/', name: 'app_bill_index', methods: ['GET'])]
    public function index(Request $request, BillRepository $billRepository, BillService $billService): Response
    {
        $searchQuery = $request->query->get('q');
        $archivedFilter = $request->query->get('archived');
        $sortBy = $request->query->get('sort_by');
        $sortDirection = $request->query->get('direction', 'ASC');

        $archivedFilter = $archivedFilter == '' ? 0 : (int)$archivedFilter;

        $bills = $billRepository->findAllWithFiltersAndSorting(
            $searchQuery,
            $archivedFilter,
            $sortBy,
            $sortDirection
        );
        $upcomingBills = $billService->checkUpcomingBills();

        return $this->render('bill/indexFront.html.twig', [
            'bills' => $bills,
            'search_query' => $searchQuery,
            'archived_filter' => $archivedFilter,
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'upcoming_bills' => $upcomingBills,
            'bill_service' => $billService
        ]);
    }

    #[Route('/new', name: 'app_bill_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $bill = new Bill();
        $form = $this->createForm(BillType::class, $bill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($bill);
            $entityManager->flush();

            return $this->redirectToRoute('app_bill_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bill/new.html.twig', [
            'bill' => $bill,
            'form' => $form,
        ]);
    }

    #[Route('/{billid}', name: 'app_bill_show', methods: ['GET'])]
    public function show(Bill $bill): Response
    {
        return $this->render('bill/showFront.html.twig', [
            'bill' => $bill,
        ]);
    }

    #[Route('/{billid}/edit', name: 'app_bill_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Bill $bill, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BillType::class, $bill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_bill_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bill/edit.html.twig', [
            'bill' => $bill,
            'form' => $form,
        ]);
    }

    #[Route('/{billid}/{redirect_route}', name: 'app_bill_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Bill $bill,
        EntityManagerInterface $entityManager,
        string $redirect_route = 'app_bill_index'
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $bill->getBillid(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($bill);
            $entityManager->flush();
        }

        return $this->redirectToRoute($redirect_route, [], Response::HTTP_SEE_OTHER);
    }
}
