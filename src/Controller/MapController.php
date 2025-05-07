<?php
namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
class MapController extends AbstractController
{
    #[Route('/map-only', name: 'app_map_only')]
public function mapOnly(EventRepository $eventRepository): Response
{
    $event = $eventRepository->findAll();
    $markers = [];
    
    foreach ($event as $ev) {
        if ($ev->getLatitude() !== null && $ev->getLongitude() !== null) {
            $markers[] = [
                'id' => $ev->getEventId(), // ✅ Ajoute l'ID ici
                'latitude' => $ev->getLatitude(),
                'longitude' => $ev->getLongitude()
            ];
        }
    }
    
    
    // Pour une meilleure sécurité, stockez votre clé API dans votre fichier .env
    // et récupérez-la ici avec $apiKey = $this->getParameter('openweathermap_api_key');
    
    return $this->render('map/map_only.html.twig', [
        'markers' => $markers,
    ]);
}
#[Route('/delete-event/{id}', name: 'delete_event', methods: ['GET', 'DELETE'])]

    public function deleteEvent(int $id, EventRepository $eventRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            return new JsonResponse(['message' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        

        $entityManager->remove($event);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Event deleted successfully',
            'id' => $id // ✅ Send ID so frontend knows which marker to remove
        ]);
    }
}