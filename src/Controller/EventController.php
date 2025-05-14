<?php

namespace App\Controller;

use App\Service\TwilioService;
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
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;
use Endroid\QrCode\Builder\BuilderInterface;
use GuzzleHttp\Client;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Writer\PngWriter;

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

    #[Route('/event/list', name: 'app_event_list')]
    public function list(
        Request $request,
        EventRepository $eventRepository,
        PaginatorInterface $paginator,
        EntityManagerInterface $entityManager
    ) {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'startDate'); // default to startDate
    
        // Validate sort fields to avoid errors
        $allowedSortFields = ['startDate', 'fee', 'name'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'startDate';
        }
    
        $qb = $eventRepository->createQueryBuilder('e');
    
        if ($search) {
            $qb->andWhere('e.name LIKE :search OR e.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
    
        $qb->orderBy("e.$sort", 'ASC'); // safe because it's validated above
    
        $pagination = $paginator->paginate(
            $qb, 
            $request->query->getInt('page', 1),
            6
        );
    
        return $this->render('event/list.html.twig', [
            'pagination' => $pagination,
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
public function new(
    Request $request,
    EntityManagerInterface $entityManager,
    TwilioService $twilioService
    ): Response {

    $event = new Event();
    $form = $this->createForm(EventType::class, $event);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $newFilename = uniqid().'.'.$imageFile->guessExtension();
            $imageFile->move('C:\Users\ayoub\Desktop\FINT\EventPlanner-3A1-Web\public\images', $newFilename);
            $event->setImage('/images/' . $newFilename);
        }
        
        // Add latitude and longitude handling
        $latitude = $request->request->get('latitude');
        $longitude = $request->request->get('longitude');
        
        if ($latitude !== null && $longitude !== null) {
            $event->setLatitude((float)$latitude);
            $event->setLongitude((float)$longitude);
        }

        $entityManager->persist($event);
        $entityManager->flush();
        
            // SMS sending logic
            $sid = 'ACaba77eb7d59bdaa0f2691c38e13bd1a2';
            $token = 'bd0b061e3a94be97ba7520726806f790';
            $fromNumber = '+13203732347';
            $toNumber = '+21653989935'; // Replace with actual recipient
    
            $client = new \Twilio\Rest\Client($sid, $token);
    
            $smsBody = "🎉 New Event Created:\n"
                     . "📌 Name: " . $event->getName() . "\n"
                     . "📝 Description: " . $event->getDescription() . "\n"
                     . "💰 Fee: " . $event->getFee() . " TND";
    
            try {
                $client->messages->create($toNumber, [
                    'from' => $fromNumber,
                    'body' => $smsBody
                ]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'SMS failed to send: ' . $e->getMessage());
            }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('event/new.html.twig', [
        'event' => $event,
        'form' => $form->createView(),
    ]);
}
    #[Route('/{eventId}', name: 'app_event_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['eventId' => 'eventId'])] Event $event): Response
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
                $imageFile->move('C:\Users\ayoub\Desktop\FINT\EventPlanner-3A1-Web\public\images', $newFilename);
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
public function newFront(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $uploadDirectory = 'C:\Users\ayoub\Desktop\FINT\EventPlanner-3A1-Web\public\images';
    
                try {
                    $imageFile->move($uploadDirectory, $newFilename);
                    $event->setImage('/images/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Image upload failed.');
                    return $this->redirectToRoute('app_event_new_front');
                }
            }
    
            $entityManager->persist($event);
            $entityManager->flush();
    
           
    
            $this->addFlash('success', 'Event created successfully!');
            return $this->redirectToRoute('app_event_indexx', ['id' => $event->getEventId()]);
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
                $uploadDirectory = 'C:\Users\ayoub\Desktop\FINT\EventPlanner-3A1-Web\public\images';
                
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

    #[Route('/export/pdf', name: 'app_events_pdf')]
public function generatePdf(EventRepository $eventRepository): Response
{
    $events = $eventRepository->findAll();
    $eventsWithDataImages = [];

    foreach ($events as $event) {
        $eventData = [
            'eventId' => $event->getEventId(),
            'name' => $event->getName(),
            'startDate' => $event->getStartDate(),
            'endDate' => $event->getEndDate(),
            'maxParticipants' => $event->getMaxParticipants(),
            'description' => $event->getDescription(),
            'fee' => $event->getFee(),
            'longitude' => $event->getLongitude(),
            'latitude' => $event->getLatitude(),
            'imageBase64' => null
        ];

        // Convert image to base64 if exists
        if ($event->getImage()) {
            $imagePath = $this->getParameter('kernel.project_dir') . '/public' . $event->getImage();
            if (file_exists($imagePath)) {
                $imageData = file_get_contents($imagePath);
                $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
                $mimeType = $this->getMimeType($extension);
                $eventData['imageBase64'] = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
        }

        $eventsWithDataImages[] = $eventData;
    }

    // Dompdf options
    $pdfOptions = new Options();
    $pdfOptions->set('defaultFont', 'Arial');
    $pdfOptions->set('isHtml5ParserEnabled', true);
    $pdfOptions->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($pdfOptions);

    // Load HTML view with event data
    $html = $this->renderView('event/pdf.html.twig', [
        'events' => $eventsWithDataImages
    ]);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Return response with PDF content
    return new Response(
        $dompdf->output(),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="events.pdf"'
        ]
    );
}

private function getMimeType(string $extension): string
{
    return match (strtolower($extension)) {
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        default => 'application/octet-stream',
    };
}


    #[Route("/evenement/search", name: "evenement_search")]
    public function searchEvent(Request $request, EventRepository $eventRepository): JsonResponse
    {{
    try {
        $searchTerm = $request->query->get('search', '');
        $searchTerm = trim($searchTerm);
        
        // Return empty array if search term is too short
        if (strlen($searchTerm) < 3) {
            return new JsonResponse([]);
        }
        
        $events = $eventRepository->searchByTerm($searchTerm);
        
        $data = [];
        foreach ($events as $event) {
            $data[] = [
                'eventId' => $event->getEventId(),
                'name' => $event->getName(),
                'lieu' => $event->getLieu(),
                'fee' => $event->getFee(),
                'description' => $event->getDescription(),
                'maxParticipants' => $event->getMaxParticipants(),
                'image' => $event->getImage() 
            ];
        }
        
        return new JsonResponse($data);
        
    } catch (\Exception $e) {
        // Log the error for debugging
        error_log($e->getMessage());
        return new JsonResponse(
            ['error' => 'Une erreur est survenue lors de la recherche'],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
}
}