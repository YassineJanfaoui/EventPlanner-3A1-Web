<?php

namespace App\Controller;

use App\Repository\EquipmentRepository;
use App\Repository\BillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;


class ResourcesController extends AbstractController
{
    private string $groqApiKey = "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";

    public function __construct() {}

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

        // Generate AI descriptions
        $aiDescriptions = [
            'category' => $this->generateAiDescription($categoryStats, 'equipment categories'),
            'state' => $this->generateAiDescription($stateStats, 'equipment states'),
            'payment' => $this->generateAiDescription($paymentStats, 'payment statuses')
        ];

        return $this->render('equipment/statistics.html.twig', [
            'categoryStats' => $categoryStats,
            'stateStats' => $stateStats,
            'paymentStats' => $paymentStats,
            'aiDescriptions' => $aiDescriptions
        ]);
    }
    #[Route('/api/bills_unpaid', name: 'app_bills_unpaid', methods: ['GET'])]
    public function handleBillsUnpaid(Request $request, BillRepository $billRepository): JsonResponse
    {
        try {
            $bills = $billRepository->createQueryBuilder('b')
                ->where('b.PaymentStatus = :status')
                ->setParameter('status', 'Pending')
                ->orderBy('b.DueDate', 'ASC')
                ->setMaxResults(5)
                ->getQuery()
                ->getResult();

            $response = [];
            foreach ($bills as $bill) {
                $response[] = [
                    'billid' => $bill->getBillid(),
                    'amount' => $bill->getAmount(),
                    'dueDate' => $bill->getDueDate()->format('Y-m-d'),
                    'daysUntilDue' => (new \DateTime())->diff($bill->getDueDate())->days,
                    'description' => $bill->getDescription()
                ];
            }

            return new JsonResponse($response);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/api/equipment/flame-alert', name: 'app_equipment_flame_alert', methods: ['GET'])]
    public function handleFlameAlert(Request $request, EntityManagerInterface $entityManager, EquipmentRepository $equipmentRepository): Response
    {
        $equipmentId = $request->query->get('equipment_id');

        if (!$equipmentId) {
            return $this->json(['error' => 'Missing equipment_id parameter'], Response::HTTP_BAD_REQUEST);
        }

        $equipment = $equipmentRepository->find($equipmentId);
        if (!$equipment) {
            return $this->json(['error' => 'Equipment not found'], Response::HTTP_NOT_FOUND);
        }

        $equipment->setState('maintenance');
        $entityManager->flush();

        return $this->json([
            'message' => 'Flame detected! Equipment set to emergency mode.',
            'equipment_id' => $equipment->getEquipmentId(),
            'new_state' => $equipment->getState()
        ]);
    }
    #[Route('/api/equipment/gas-level', name: 'app_equipment_gas_level', methods: ['GET'])]
    public function handleGasLevel(Request $request, EntityManagerInterface $entityManager, EquipmentRepository $equipmentRepository): Response
    {
        $data = json_decode($request->getContent(), true);


        if (!isset($data['equipment_id']) || !isset($data['gas_level'])) {
            return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $equipment = $equipmentRepository->find($data['equipment_id']);
        if (!$equipment) {
            return $this->json(['error' => 'Equipment not found'], Response::HTTP_NOT_FOUND);
        }

        $equipment->setState('maintenance');
        $entityManager->flush();

        return $this->json([
            'message' => 'Gas level threshold exceeded! Equipment set to maintenance.',
            'equipment_id' => $equipment->getEquipmentId(),
            'new_state' => $equipment->getState()
        ]);
    }
    #[Route('/api/statistics/equipment', name: 'app_statistics_resources_api', methods: ['GET'])]
    public function equipmentStatisticsAPI(EquipmentRepository $equipmentRepository, BillRepository $billRepository): JsonResponse
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

        return $this->json([
            'categoryStats' => $categoryStats,
            'stateStats' => $stateStats
        ]);
    }
    private function generateAiDescription(array $stats, string $type): string
    {
        $client = HttpClient::create();

        $prompt = sprintf(
            "Analyze this %s distribution statistics: %s. " .
                "Provide a 2 short sentence professional summary highlighting key insights in simple language.",
            $type,
            json_encode($stats)
        );

        try {
            $response = $client->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->groqApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 150
                ]
            ]);

            $content = $response->getContent(false); // disables exception on non-2xx
            dump($content);
            $data = json_decode($content, true);
            dump($data);
            return $data['choices'][0]['message']['content'] ?? 'No description available';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
