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
use App\Repository\EventEquipmentRepository;
use Knp\Snappy\Pdf;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Mercure\HubInterface;
use Knp\Component\Pager\PaginatorInterface;



#[Route('/bill')]
final class BillController extends AbstractController
{
    #[Route('/', name: 'app_bill_index', methods: ['GET'])]
    public function index(
        Request $request,
        BillRepository $billRepository,
        BillService $billService,
        PaginatorInterface $paginator,
        HubInterface $hub,
        NotifierInterface $notifier
    ): Response {
        $searchQuery = $request->query->get('q');
        $archivedFilter = $request->query->get('archived');
        $sortBy = $request->query->get('sort_by');
        $sortDirection = $request->query->get('direction', 'ASC');

        $archivedFilter = $archivedFilter == '' ? 0 : (int)$archivedFilter;

        $query = $billRepository->createQueryBuilderWithFiltersAndSorting(
            $searchQuery,
            $archivedFilter,
            $sortBy,
            $sortDirection
        );

        $bills = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        $upcomingBills = $billService->checkUpcomingBills();

        if (count($upcomingBills) > 0 && !$request->isXmlHttpRequest() && !$request->query->get('ajax')) {
            $this->addFlash('upcoming_bills', [
                'message' => 'You have ' . count($upcomingBills) . ' bill(s) due within a week',
                'count' => count($upcomingBills),
            ]);
        }

        if ($request->isXmlHttpRequest() || $request->query->get('ajax')) {
            return $this->render('bill/_table.html.twig', [
                'bills' => $bills,
                'search_query' => $searchQuery,
                'archived_filter' => $archivedFilter,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
                'bill_service' => $billService
            ]);
        }

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
    #[Route('/generate/{eventId}', name: 'app_bill_generate', methods: ['GET', 'POST'])]
    public function generate(
        int $eventId,
        EntityManagerInterface $entityManager,
        EventEquipmentRepository $eventEquipmentRepository
    ): Response {
        $eventEquipments = $eventEquipmentRepository->findBy(['event' => $eventId]);

        if (empty($eventEquipments)) {
            $this->addFlash('warning', 'No equipment found for this event.');
            return $this->redirectToRoute('app_bill_index');
        }

        $event = $eventEquipments[0]->getEvent(); // Get event from first equipment
        $totalAmount = 0;
        $description = "Equipment for event: " . $event->getName() . "\n";

        foreach ($eventEquipments as $eventEquipment) {
            $equipment = $eventEquipment->getEquipment();
            if ($equipment) {
                $price = $equipment->getPrice() ?? 0;
                $quantity = $equipment->getQuantity() ?? 1;
                $totalAmount += $price * $quantity;

                $description .= sprintf(
                    "%s (Qty: %d @ %s each)\n",
                    $equipment->getName(),
                    $quantity,
                    $price
                );
            }
        }

        if ($totalAmount > 0) {
            $bill = new Bill();
            $bill->setEvent($event);
            $bill->setAmount($totalAmount);
            $bill->setDescription($description);
            $bill->setDueDate(new \DateTime('+15 days'));
            $bill->setPaymentStatus('pending');
            $bill->setArchived(0);

            $entityManager->persist($bill);
            $entityManager->flush();

            $this->addFlash('success', 'Bill generated successfully!');
        } else {
            $this->addFlash('warning', 'No billable equipment found for this event.');
        }

        return $this->redirectToRoute('app_bill_index');
    }
    #[Route('/{billid}/pdf', name: 'app_bill_pdf', methods: ['GET'])]
    public function generatePdf(Bill $bill, Pdf $knpSnappyPdf)
    {
        $filename = sprintf('bill-%d-%s.pdf', $bill->getBillid(), date('Y-m-d'));
        $path = $this->getParameter('kernel.project_dir') . '/public/pdf/' . $filename;

        $imagePath = realpath($this->getParameter('kernel.project_dir') . '/public/pdf/1.png');
        $imagePath = 'file:///' . str_replace('\\', '/', $imagePath); // Windows path fix

        $html = $this->renderView('bill/pdf_template.html.twig', [
            'bill' => $bill,
            'stampPath' => $imagePath,
        ]);

        $knpSnappyPdf->generateFromHtml($html, $path, [
            'encoding' => 'utf-8',
            'images' => true,
            'enable-local-file-access' => true,
        ], true);

        return $this->file($path);
    }
}
