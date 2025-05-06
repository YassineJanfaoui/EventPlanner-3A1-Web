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
use Knp\Component\Pager\PaginatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;



#[Route('/partner')]
final class PartnerController extends AbstractController
{
    #[Route(name: 'app_partner_index', methods: ['GET'])]
    public function index(Request $request, PartnerRepository $partnerRepository, PaginatorInterface $paginator): Response
    {
        $searchQuery = $request->query->get('search');
        $coachFilter = $request->query->get('coach');
        $sortBy = $request->query->get('sortBy');
        $sortDirection = strtoupper($request->query->get('sortDirection', 'ASC'));

        $partner = $partnerRepository->findAllWithFiltersAndSorting(
            $searchQuery,
            $coachFilter,
            $sortBy,
            $sortDirection
        );

        $pagination = $paginator->paginate(
            $partner, // QueryBuilder
            $request->query->getInt('page', 1), // Current page
            2 // Items per page
        );

        return $this->render('partner/index.html.twig', [
            'partner' => $pagination,
            'partners' => $pagination, // update to use pagination in view
        ]);
    }

    #[Route('/stats', name: 'partner_stats')]
public function stat(PartnerRepository $partnerRepository): Response
{
    $chartPath = $this->getParameter('kernel.project_dir') . '/public/chart.png';
    
    if (file_exists($chartPath)) {
        unlink($chartPath);
    }
    
    require_once $this->getParameter('kernel.project_dir') . '/lib/jpgraph/src/jpgraph.php';
    require_once $this->getParameter('kernel.project_dir') . '/lib/jpgraph/src/jpgraph_pie.php';
    require_once $this->getParameter('kernel.project_dir') . '/lib/jpgraph/src/jpgraph_pie3d.php';
    
    $qb = $partnerRepository->createQueryBuilder('p')
        ->select('p.category, COUNT(p) as count')
        ->groupBy('p.category')
        ->getQuery();
    
    $results = $qb->getResult();
    $categories = array_column($results, 'category');
    $counts = array_map('intval', array_column($results, 'count'));
    
    // Enhanced chart settings
    $graph = new \PieGraph(800, 500, 'auto');
    $graph->SetShadow(2, 2, '#cccccc', 5);
    $graph->title->Set('Partner Distribution by Category');
    $graph->title->SetFont(FF_ARIAL, FS_BOLD, 14);
    $graph->SetFrame(false);
    $graph->SetMarginColor('#ffffff');
    
    $p1 = new \PiePlot3D($counts);
    $p1->SetLegends($categories);
    $p1->SetTheme('pastel');
    $p1->ExplodeSlice(0); // Explode the largest slice
    $p1->value->SetFont(FF_ARIAL, FS_NORMAL, 10);
    $p1->value->SetColor('#333333');
    $p1->SetLabelType(PIE_VALUE_ABS);
    $p1->SetLabels($counts, 1);
    $p1->SetCenter(0.4, 0.5);
    
    $graph->Add($p1);
    $graph->Stroke($chartPath);
    
    // Pass additional statistics to the template
    $totalPartners = array_sum($counts);
    $categoryPercentages = array_map(function($count) use ($totalPartners) {
        return round(($count / $totalPartners) * 100, 1);
    }, $counts);
    
    return $this->render('partner/stats.html.twig', [
        'categories' => $categories,
        'counts' => $counts,
        'percentages' => array_combine($categories, $categoryPercentages),
        'totalPartners' => $totalPartners
    ]);
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
