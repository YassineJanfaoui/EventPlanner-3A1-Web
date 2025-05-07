<?php

namespace App\Controller;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadImageController extends AbstractController
{
    #[Route('/event/upload-image', name: 'app_event_upload_image', methods: ['POST'])]
    public function uploadImage(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): JsonResponse {
        $eventId = $request->request->get('eventId');
        
        if (!$eventId) {
            return $this->json([
                'success' => false,
                'message' => 'ID de l\'événement manquant'
            ], 400);
        }

        $event = $entityManager->getRepository(Event::class)->find($eventId);

        if (!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Événement non trouvé'
            ], 404);
        }

        $imageFile = $request->files->get('image');

        if (!$imageFile) {
            return $this->json([
                'success' => false,
                'message' => 'Aucune image fournie'
            ], 400);
        }

        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid('', true) . '.' . $imageFile->guessExtension();

        try {
            $uploadDir = $this->getParameter('images_directory');
            $imageFile->move($uploadDir, $newFilename);

            $event->setImage('images/' . $newFilename);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Image téléchargée avec succès',
                'path' => 'images/' . $newFilename
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement de l\'image : ' . $e->getMessage()
            ], 500);
        }
    }
}
