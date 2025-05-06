<?php

namespace App\Controller;

use App\Entity\Equipment;
use App\Form\EquipmentType;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Knp\Component\Pager\PaginatorInterface;



#[Route('/equipment')]
final class EquipmentController extends AbstractController
{
    #[Route(name: 'app_equipment_index', methods: ['GET'])]
    public function index(
        Request $request,
        EquipmentRepository $equipmentRepository,
        PaginatorInterface $paginator
    ): Response {
        $searchQuery = $request->query->get('q');
        $CategoryFilter = $request->query->get('category');
        $sortBy = $request->query->get('sort_by');
        $sortDirection = $request->query->get('direction', 'ASC');

        $CategoryFilter = ($CategoryFilter == '' || $CategoryFilter == 'all') ? null : $CategoryFilter;

        $query = $equipmentRepository->createQueryBuilderWithFiltersAndSorting(
            $searchQuery,
            $CategoryFilter,
            $sortBy,
            $sortDirection
        );

        $equipments = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5 // Items per page
        );

        $writer = new PngWriter();
        $equipmentWithQrCodes = [];
        foreach ($equipments as $equipment) {
            $qrCode = new QrCode(
                data: $equipment->getQrCodeContent(),
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Low,
                size: 300,
                margin: 10,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
                foregroundColor: new Color(0, 0, 0),
                backgroundColor: new Color(255, 255, 255)
            );

            $result = $writer->write($qrCode);

            $equipmentWithQrCodes[] = [
                'equipment' => $equipment,
                'qrCode' => 'data:image/png;base64,' . base64_encode($result->getString())
            ];
        }

        if ($request->isXmlHttpRequest() || $request->query->get('ajax')) {
            return $this->render('equipment/_table.html.twig', [
                'equipmentWithQrCodes' => $equipmentWithQrCodes,
                'equipments' => $equipments,
                'search_query' => $searchQuery,
                'Category_filter' => $CategoryFilter,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection
            ]);
        }

        return $this->render('equipment/indexFront.html.twig', [
            'equipmentWithQrCodes' => $equipmentWithQrCodes,
            'equipments' => $equipments,
            'search_query' => $searchQuery,
            'Category_filter' => $CategoryFilter,
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection
        ]);
    }

    #[Route('/new', name: 'app_equipment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipment = new Equipment();
        $form = $this->createForm(EquipmentType::class, $equipment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($equipment);
            $entityManager->flush();

            return $this->redirectToRoute('app_equipment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipment/new.html.twig', [
            'equipment' => $equipment,
            'form' => $form,
        ]);
    }

    #[Route('/{EquipmentId}', name: 'app_equipment_show', methods: ['GET'])]
    public function show(Equipment $equipment): Response
    {
        return $this->render('equipment/showFront.html.twig', [
            'equipment' => $equipment
        ]);
    }

    #[Route('/{EquipmentId}/edit', name: 'app_equipment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Equipment $equipment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EquipmentType::class, $equipment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_equipment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipment/edit.html.twig', [
            'equipment' => $equipment,
            'form' => $form,
        ]);
    }

    #[Route('/{EquipmentId}/{redirect_route}', name: 'app_equipment_delete', methods: ['POST'])]
    public function delete(Request $request, Equipment $equipment, EntityManagerInterface $entityManager, string $redirect_route = 'app_equipment_index'): Response
    {
        if ($this->isCsrfTokenValid('delete' . $equipment->getEquipmentId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($equipment);
            $entityManager->flush();
        }

        return $this->redirectToRoute($redirect_route, [], Response::HTTP_SEE_OTHER);
    }
}
