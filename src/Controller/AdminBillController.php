<?php

namespace App\Controller;

use App\Entity\Bill;
use App\Repository\BillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/bill')]
final class AdminBillController extends AbstractController
{
    #[Route(name: 'app_admin_bill', methods: ['GET'])]
    public function index(BillRepository $billRepository): Response
    {
        return $this->render('bill/indexBack.html.twig', [
            'bills' => $billRepository->findAll(),
        ]);
    }

    #[Route('/{billid}', name: 'app_admin_bill_show', methods: ['GET'])]
    public function show(Bill $bill): Response
    {
        return $this->render('bill/showBack.html.twig', [
            'bill' => $bill,
        ]);
    }
}
