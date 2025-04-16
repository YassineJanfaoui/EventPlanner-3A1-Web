<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
#[Route('/event')]
final class EventController extends AbstractController
{
    #[Route(name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }
    #[Route('/eventplan',name: 'app_event_indexx', methods: ['GET'])]
    public function indexx(EventRepository $eventRepository): Response
    {
        return $this->render('event/showfront.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }
    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $imageFile = $form->get('image')->getData();
                if ($imageFile) {
                    $newFilename = uniqid().'.'.$imageFile->guessExtension();
                    $imageFile->move('D:\telechargement\EventPlanner-3A1-Web-main\public\images', $newFilename);
                    $event->setImage('/images/' . $newFilename);
                }
               /*else
               { $event->setImage('/images/OIP.jpg' );}*/
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{eventId}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{eventId}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        // Create the form for editing the event
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Get the image file from the form if it's updated
            $imageFile = $form->get('image')->getData();
    
            if ($imageFile) {
                // If an image is uploaded, process it and save it
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move('D:/telechargement/EventPlanner-3A1-Web-main/public/images', $newFilename);
                // Update the event image path
                $event->setImage('/images/' . $newFilename);
            }
    
            // Save the changes to the database
            $entityManager->flush();
    
            // Add a flash message to indicate success
            $this->addFlash('success', 'Événement mis à jour avec succès.');
    
            // Redirect to the event index page
            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }
    
        // Render the form for editing the event
        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/{eventId}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getEventId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/event/list', name: 'event_list')]
public function list(Request $request, EventRepository $eventRepository): Response
{
    $search = $request->query->get('search');
    $sort = $request->query->get('sort', 'date'); // Default sort by start date

    $queryBuilder = $eventRepository->createQueryBuilder('e');

    if ($search) {
        $queryBuilder
            ->andWhere('e.name LIKE :search OR e.description LIKE :search')
            ->setParameter('search', '%' . $search . '%');
    }

    // Update sorting to match your database fields
    switch ($sort) {
        case 'fee':
            $queryBuilder->orderBy('e.fee', 'ASC');
            break;
        case 'name':
            $queryBuilder->orderBy('e.name', 'ASC');
            break;
        default: // 'date' or any other value
            $queryBuilder->orderBy('e.startDate', 'ASC');
            break;
    }

    $events = $queryBuilder->getQuery()->getResult();

    return $this->render('event/list.html.twig', [
        'events' => $events,
    ]);
}
#[Route('/{eventId}/reservations', name: 'event_reservations', methods: ['GET'])]
public function getReservationsByEvent(
    Event $event,
    ReservationRepository $reservationRepository
): Response {
    // Find all reservations where this event is linked
    $reservations = $reservationRepository->findBy(['event' => $event]);

    return $this->render('event/showdetails.html.twig', [
        'event' => $event,
        'reservations' => $reservations,
    ]);
}

/**
     * @Route("/events/new", name="app_event_new_front", methods={"GET", "POST"})
     */
    #[Route('/events/new', name: 'app_event_new_front', methods: ['GET', 'POST'])]
public function newFront(Request $request, EntityManagerInterface $entityManager): Response
{
    $event = new Event();
    $form = $this->createForm(EventType::class, $event);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();
        
        if ($imageFile) {
            $newFilename = uniqid().'.'.$imageFile->guessExtension();
            
            // Hardcoded path (same as your working `new()` method)
            $uploadDirectory = 'D:\telechargement\EventPlanner-3A1-Web-main\public\images';
            
            try {
                $imageFile->move(
                    $uploadDirectory, // Use hardcoded path
                    $newFilename
                );
                // Match the path format in `new()`: '/images/filename.jpg'
                $event->setImage('/images/' . $newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Image upload failed.');
                return $this->redirectToRoute('app_event_new_front');
            }
        }

        $entityManager->persist($event);
        $entityManager->flush();

        $this->addFlash('success', 'Event created successfully!');
        return $this->redirectToRoute('app_reservation_index', ['id' => $event->getEventId()]);
    }

    return $this->render('event/newfront.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route('/events/{id}/details', name: 'app_event_details_front', methods: ['GET'])]
    public function detailsFront(
        Event $event,
        ReservationRepository $reservationRepository,
        EventRepository $eventRepository
    ): Response {
        $reservations = $reservationRepository->findBy(['event' => $event]);
        
        $similarEvents = $eventRepository->createQueryBuilder('e')
            ->andWhere('e.eventId != :currentId')
            ->setParameter('currentId', $event->getEventId())
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        return $this->render('event/detailsfront.html.twig', [
            'event' => $event,
            'reservations' => $reservations,
            'similarEvents' => $similarEvents,
            'availableSpots' => $event->getMaxParticipants() - count($reservations)
        ]);
    }

    #[Route('/event/{id}/editt', name: 'app_event_editt', methods: ['GET', 'POST'])]
    public function editt(
        Request $request,
        Event $event,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image removal
            if ($request->request->get('remove_image')) {
                $oldImagePath = $event->getImage();
                if ($oldImagePath) {
                    $fullPath = $this->getParameter('kernel.project_dir').'/public'.$oldImagePath;
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                    $event->setImage(null);
                }
            }

            // Handle new image upload
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $uploadDirectory = 'D:\telechargement\EventPlanner-3A1-Web-main\public\images';
                
                // Remove old image if exists
                $oldImagePath = $event->getImage();
                if ($oldImagePath) {
                    $fullPath = $this->getParameter('kernel.project_dir').'/public'.$oldImagePath;
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }

                // Upload new image
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $uploadDirectory,
                        $newFilename
                    );
                    $event->setImage('/images/'.$newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'There was an error uploading your image.');
                    return $this->redirectToRoute('app_event_editt', ['id' => $event->getEventId()]);
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Event updated successfully!');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getEventId()]);
        }

        return $this->render('event/editfront.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }
}




