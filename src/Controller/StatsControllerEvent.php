<?php

namespace App\Controller;

use App\Repository\EventRepository;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\ColumnChart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StatsControllerEvent extends AbstractController
{
    #[Route('/stats/events-by-fee', name: 'app_stats_events_by_fee')]
    public function eventsByFee(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();
        
        $feeRanges = [
            '0-50' => 0,
            '51-100' => 0,
            '101-200' => 0,
            '201-500' => 0,
            '501+' => 0
        ];
        
        foreach ($events as $event) {
            $fee = $event->getFee();
            
            if ($fee <= 50) {
                $feeRanges['0-50']++;
            } elseif ($fee <= 100) {
                $feeRanges['51-100']++;
            } elseif ($fee <= 200) {
                $feeRanges['101-200']++;
            } elseif ($fee <= 500) {
                $feeRanges['201-500']++;
            } else {
                $feeRanges['501+']++;
            }
        }
        
        $data = [['Plage de frais', 'Nombre d\'événements']];
        foreach ($feeRanges as $range => $count) {
            $data[] = [$range, $count];
        }
        
        $chart = new ColumnChart();
        $chart->getData()->setArrayToDataTable($data);
        $chart->getOptions()->setTitle('Répartition des événements par plage de frais');
        $chart->getOptions()->getHAxis()->setTitle('Plage de frais');
        $chart->getOptions()->getVAxis()->setTitle('Nombre d\'événements');
        $chart->getOptions()->setHeight(400);
        $chart->getOptions()->setWidth(1000);
        $chart->getOptions()->setColors(['#4285F4']);
        $chart->getOptions()->getLegend()->setPosition('none');
        $chart->getOptions()->getHAxis()->getTextStyle()->setBold(true);
        $chart->getOptions()->getVAxis()->getTextStyle()->setBold(true);

        
return $this->render('stats/events_by_fee.html.twig', [
    'chart' => $chart,
    'feeRanges' => $feeRanges,
]);
    }
}