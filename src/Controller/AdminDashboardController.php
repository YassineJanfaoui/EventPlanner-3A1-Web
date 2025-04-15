<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\Event;
use App\Entity\EventTeam;
use App\Entity\Feedback;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminDashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Get counts for dashboard statistics
        $teamCount = $entityManager->getRepository(Team::class)->count([]);
        $eventTeamCount = $entityManager->getRepository(EventTeam::class)->count([]);
        $feedbackCount = $entityManager->getRepository(Feedback::class)->count([]);
        $eventCount = $entityManager->getRepository(Event::class)->count([]);
        
        return $this->render('back/dashboard.html.twig', [
            'teams' => $teamCount,
            'eventTeams' => $eventTeamCount,
            'feedbacks' => $feedbackCount,
            'events' => $eventCount,
        ]);
    }
}