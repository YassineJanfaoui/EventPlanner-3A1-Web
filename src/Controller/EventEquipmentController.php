<?php

namespace App\Controller;

use App\Entity\EventEquipment;
use App\Entity\Bill;
use App\Form\EventEquipmentType;
use App\Repository\EventEquipmentRepository;
use App\Repository\EventRepository;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/eventequipment')]
final class EventEquipmentController extends AbstractController
{
    #[Route('/', name: 'app_event_equipment_index', methods: ['GET'])]
    public function index(
        EventEquipmentRepository $eventEquipmentRepository,
        EventRepository $eventRepository,
        Request $request
    ): Response {
        $selectedEventId = $request->query->get('event_id');
        $eventEquipments = [];
        $events = $eventRepository->findAll();

        if ($selectedEventId) {
            $eventEquipments = $eventEquipmentRepository->findBy(['event' => $selectedEventId]);
        } else {
            $eventEquipments = $eventEquipmentRepository->findAll();
        }

        return $this->render('event_equipment/index.html.twig', [
            'event_equipments' => $eventEquipments,
            'events' => $events,
            'selected_event_id' => $selectedEventId
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
    #[Route('/generate-all', name: 'app_bill_generate_all', methods: ['GET', 'POST'])]
    public function generateAll(EventEquipmentRepository $eventEquipmentRepo, EntityManagerInterface $entityManager)
    {
        $eventEquipments = $eventEquipmentRepo->findAll();

        if (empty($eventEquipments)) {
            $this->addFlash('warning', 'No equipment found.');
            return $this->redirectToRoute('app_bill_index');
        }

        $events = [];
        foreach ($eventEquipments as $eventEquipment) {
            $event = $eventEquipment->getEvent();
            $eventId = $event->getId();
            if (!isset($events[$eventId])) {
                $events[$eventId] = [
                    'event' => $event,
                    'equipments' => []
                ];
            }
            $events[$eventId]['equipments'][] = $eventEquipment;
        }

        foreach ($events as $eventData) {
            $event = $eventData['event'];
            $equipments = $eventData['equipments'];
            $totalAmount = 0;
            $description = "";

            foreach ($equipments as $eventEquipment) {
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

            $bill = new Bill();
            $bill->setEvent($event);
            $bill->setAmount($totalAmount);
            $bill->setDueDate(new \DateTime());
            $bill->setArchived(0);
            $bill->setPaymentStatus('Pending');
            $bill->setDescription($description);
            $entityManager->persist($bill);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Bills generated for all events.');
        return $this->redirectToRoute('app_bill_index');
    }
    #[Route('/generate-for-event/{eventId}', name: 'app_bill_generate_for_event', methods: ['GET'])]
    public function generateForEvent(
        int $eventId,
        EventEquipmentRepository $eventEquipmentRepo,
        EntityManagerInterface $entityManager,
        EventRepository $eventRepository
    ): Response {
        $event = $eventRepository->find($eventId);
        if (!$event) {
            $this->addFlash('error', 'Event not found');
            return $this->redirectToRoute('app_event_equipment_index');
        }

        $eventEquipments = $eventEquipmentRepo->findBy(['event' => $eventId]);

        if (empty($eventEquipments)) {
            $this->addFlash('warning', 'No equipment found for this event.');
            return $this->redirectToRoute('app_event_equipment_index');
        }

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

        $bill = new Bill();
        $bill->setEvent($event);
        $bill->setAmount($totalAmount);
        $bill->setDescription($description);
        $bill->setDueDate(new \DateTime('+14 days'));
        $bill->setPaymentStatus('pending');
        $bill->setArchived(0);

        $entityManager->persist($bill);
        $entityManager->flush();

        $this->addFlash('success', 'Bill generated successfully for event: ' . $event->getName());
        return $this->redirectToRoute('app_bill_index');
    }
}
