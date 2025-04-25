<?php

namespace App\Controller;

use App\Entity\Partner;
use App\Form\PartnerType;
use App\Repository\PartnerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;



#[Route('/partner')]
final class PartnerController extends AbstractController
{
    #[Route(name: 'app_partner_index', methods: ['GET'])]
    public function index(Request $request, PartnerRepository $workshopRepository): Response
{
    $searchQuery = $request->query->get('search');
    $coachFilter = $request->query->get('coach');
    $sortBy = $request->query->get('sortBy');
    $sortDirection = strtoupper($request->query->get('sortDirection', 'ASC'));

    $workshops = $workshopRepository->findAllWithFiltersAndSorting(
        $searchQuery,
        $coachFilter,
        $sortBy,
        $sortDirection
    );

    return $this->render('partner/index.html.twig', [
        'partners' => $workshops
    ]);
}



    #[Route('/stats', name: 'partner_stats')]
    public function stat(PartnerRepository $partnerRepository): Response
    {
        // Path to overwrite
        $chartPath = $this->getParameter('kernel.project_dir') . '/public/chart.png';
    
        // Check if file exists and delete it if so (optional)
        if (file_exists($chartPath)) {
            unlink($chartPath);  // Delete the old chart image
        }
    
        // Include JpGraph files
        require_once $this->getParameter('kernel.project_dir') . '/lib/jpgraph/src/jpgraph.php';
        require_once $this->getParameter('kernel.project_dir') . '/lib/jpgraph/src/jpgraph_pie.php';
    
        // Get data
        $qb = $partnerRepository->createQueryBuilder('p')
            ->select('p.category, COUNT(p) as count')
            ->groupBy('p.category')
            ->getQuery();
    
        $results = $qb->getResult();
        $categories = array_column($results, 'category');
        $counts = array_map('intval', array_column($results, 'count'));
    
        // Create the chart
        $graph = new \PieGraph(600, 400);
        $graph->SetShadow();
    
        $p1 = new \PiePlot($counts);
        $p1->SetLegends($categories);
        $graph->Add($p1);
    
        // Overwrite the chart image
        $graph->Stroke($chartPath);
    
        // Just render the page with the chart without redirection
        return $this->render('partner/stats.html.twig');
    }
    
    
    

    #[Route('/new', name: 'app_partner_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $partner = new Partner();
        $form = $this->createForm(PartnerType::class, $partner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($partner);
            $entityManager->flush();

            return $this->redirectToRoute('app_partner_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partner/new.html.twig', [
            'partner' => $partner,
            'form' => $form,
        ]);
    }

    #[Route('/{partnerId}', name: 'app_partner_show', methods: ['GET'])]
    public function show(Partner $partner): Response
    {
        return $this->render('partner/show.html.twig', [
            'partner' => $partner,
        ]);
    }

    #[Route('/{partnerId}/edit', name: 'app_partner_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Partner $partner, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PartnerType::class, $partner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_partner_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partner/edit.html.twig', [
            'partner' => $partner,
            'form' => $form,
        ]);
    }

    #[Route('/{partnerId}', name: 'app_partner_delete', methods: ['POST'])]
    public function delete(Request $request, Partner $partner, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$partner->getPartnerId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($partner);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_partner_index', [], Response::HTTP_SEE_OTHER);
    }

    
}
