<?php

namespace App\Controller;

use App\Repository\EquipmentRepository;
use App\Repository\BillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResourcesController extends AbstractController
{
    #[Route('/statistics/equipment', name: 'app_statistics_resources')]
    public function equipmentStatistics(EquipmentRepository $equipmentRepository, BillRepository $billRepository): Response
    {
        $categoryStats = $equipmentRepository->createQueryBuilder('e')
            ->select('e.category as name, COUNT(e.EquipmentId) as count')
            ->groupBy('e.category')
            ->getQuery()
            ->getResult();

        $stateStats = $equipmentRepository->createQueryBuilder('e')
            ->select('e.state as name, COUNT(e.EquipmentId) as count')
            ->groupBy('e.state')
            ->getQuery()
            ->getResult();

        $paymentStats = $billRepository->createQueryBuilder('b')
            ->select('b.PaymentStatus as name, COUNT(b.billid) as count')
            ->groupBy('b.PaymentStatus')
            ->getQuery()
            ->getResult();

        return $this->render('equipment/statistics.html.twig', [
            'categoryStats' => $categoryStats,
            'stateStats' => $stateStats,
            'paymentStats' => $paymentStats,
        ]);
    }
}
