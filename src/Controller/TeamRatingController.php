<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamRatingType;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/team/rating')]
class TeamRatingController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_team_rating_index', methods: ['GET'])]
    public function index(Request $request, TeamRepository $teamRepository): Response
    {
        // Get all teams with their ratings
        $teams = $teamRepository->findAll();
        
        return $this->render('front/team_rating/index.html.twig', [
            'teams' => $teams,
        ]);
    }

    #[Route('/{id}', name: 'app_team_rating_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TeamRatingType::class, $team);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Team rating updated successfully!');
            return $this->redirectToRoute('app_team_rating_index');
        }
        
        return $this->render('front/team_rating/edit.html.twig', [
            'team' => $team,
            'form' => $form,
        ]);
    }
    
    #[Route('/batch/update', name: 'app_team_rating_batch_update', methods: ['GET', 'POST'])]
    public function batchUpdate(Request $request, TeamRepository $teamRepository, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $scores = $request->request->get('scores', []);
            $ranks = $request->request->get('ranks', []);
            
            foreach ((array)$scores as $teamId => $score) {
                $team = $teamRepository->find($teamId);
                if ($team) {
                    $team->setScore(floatval($score));
                    if (isset($ranks[$teamId])) {
                        $team->setRank(intval($ranks[$teamId]));
                    }
                }
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Team ratings updated successfully!');
            
            return $this->redirectToRoute('app_team_rating_index');
        }
        
        $teams = $teamRepository->findAll();
        
        return $this->render('front/team_rating/batch_update.html.twig', [
            'teams' => $teams,
        ]);
    }
}