<?php

namespace App\Controller;

use App\Entity\EventTeam;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/admin/event-team')]
class AdminEventTeamController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_admin_event_team_index')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        // Simplified query without ordering to avoid field errors
        $query = $this->entityManager->getRepository(EventTeam::class)
            ->createQueryBuilder('et')
            ->getQuery();

        $eventTeams = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5  // Changed from 10 to 5 items per page
        );

        return $this->render('back/event_team/index.html.twig', [
            'eventTeams' => $eventTeams,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_event_team_delete')]
    public function delete(int $id): Response
    {
        $eventTeam = $this->entityManager->getRepository(EventTeam::class)->findOneBy([
            'submission_id' => $id
        ]);
        
        if (!$eventTeam) {
            throw $this->createNotFoundException('Event Team not found');
        }
        
        $this->entityManager->remove($eventTeam);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Event Team deleted successfully');
        
        return $this->redirectToRoute('app_admin_event_team_index');
    }
}