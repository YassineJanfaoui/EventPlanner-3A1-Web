<?php

namespace App\Controller;

use App\Repository\EquipmentRepository;
use App\Repository\BillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
