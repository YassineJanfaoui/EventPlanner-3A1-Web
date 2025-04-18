<?php

namespace App\Controller;

use App\Entity\EventEquipment;
use App\Form\EventEquipmentType;
use App\Repository\EventEquipmentRepository;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/eventequipment')]
final class EventEquipmentController extends AbstractController
{
    #[Route(name: 'app_event_equipment_index', methods: ['GET'])]
    public function index(EventEquipmentRepository $eventEquipmentRepository): Response
    {
        return $this->render('event_equipment/index.html.twig', [
            'event_equipments' => $eventEquipmentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_event_equipment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EquipmentRepository $equipmentRepo): Response
    {
        $eventEquipment = new EventEquipment();
        $equipmentId = $request->query->get('equipment_id');
        $equipmentPreSelected = false;

        if ($equipmentId) {
            $equipment = $equipmentRepo->find($equipmentId);
            if ($equipment) {
                $eventEquipment->setEquipment($equipment);
                $equipmentPreSelected = true;
                $equipment->setState('unavailable');
                $entityManager->persist($equipment);
            }
        }

        $form = $this->createForm(EventEquipmentType::class, $eventEquipment, [
            'equipment_pre_selected' => $equipmentPreSelected,
            'start_date' => $eventEquipment->getStartDate()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($eventEquipment);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_equipment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event_equipment/new.html.twig', [
            'event_equipment' => $eventEquipment,
            'form' => $form,
            'equipment_pre_selected' => $equipmentPreSelected
        ]);
    }

    #[Route('/{id}', name: 'app_event_equipment_show', methods: ['GET'])]
    public function show(EventEquipment $eventEquipment): Response
    {
        return $this->render('event_equipment/show.html.twig', [
            'event_equipment' => $eventEquipment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_equipment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EventEquipment $eventEquipment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventEquipmentType::class, $eventEquipment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_event_equipment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event_equipment/edit.html.twig', [
            'event_equipment' => $eventEquipment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_equipment_delete', methods: ['POST'])]
    public function delete(Request $request, EventEquipment $eventEquipment, EntityManagerInterface $entityManager): Response
    {
        $equipment = $eventEquipment->getEquipment();

        if ($equipment) {
            $equipment->setState('functional');
            $entityManager->persist($equipment);
        }

        if ($this->isCsrfTokenValid('delete' . $eventEquipment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($eventEquipment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_equipment_index', [], Response::HTTP_SEE_OTHER);
    }
}
