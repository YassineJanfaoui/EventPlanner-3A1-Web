<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/team')]
final class TeamController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route(name: 'app_team_index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $query = $this->entityManager->getRepository(Team::class)->createQueryBuilder('t')
            ->orderBy('t.id', 'DESC')
            ->getQuery();

        $teams = $paginator->paginate(
            $query, // Query
            $request->query->getInt('page', 1), // Page number
            5 // Changed from 10 to 5 items per page
        );

        return $this->render('front/team/index.html.twig', [
            'teams' => $teams,
        ]);
    }

    #[Route('/new', name: 'app_team_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $team = new Team();
        // Set default values - use 0 instead of null since the database doesn't allow nulls
        $team->setScore(0);
        $team->setRank(0);
        
        $form = $this->createForm(TeamType::class, $team, [
            'show_score_rank' => false, // Don't show score and rank fields
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($team);
            $entityManager->flush();

            $this->addFlash('success', 'Team created successfully!');
            return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/team/new.html.twig', [
            'team' => $team,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_team_show', methods: ['GET'])]
    public function show(Team $team): Response
    {
        return $this->render('front/team/show.html.twig', [
            'team' => $team,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_team_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TeamType::class, $team, [
            'show_score_rank' => false, // Don't show score and rank fields
            'csrf_protection' => false  // Disable CSRF protection for this form
        ]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Team updated successfully!');
            return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('front/team/edit.html.twig', [
            'team' => $team,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_team_delete', methods: ['POST'])]
    public function delete(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$team->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($team);
                $entityManager->flush();
                $this->addFlash('success', 'Team deleted successfully.');
            } catch (\Exception $e) {
                // Check if it's a foreign key constraint violation
                if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                    $this->addFlash('error', 'Cannot delete this team because it has associated feedback records.');
                } else {
                    $this->addFlash('error', 'An error occurred while deleting the team.');
                }
            }
        }

        return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/rate/{id}', name: 'app_team_rate', methods: ['GET', 'POST'])]
    public function rateTeam(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        // Create a form with only Score and Rank fields
        $form = $this->createForm(TeamType::class, $team, [
            'show_team_name' => true, // Don't show team name field
            'show_score_rank' => true,  // Show score and rank fields
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Team rating updated successfully!');
            return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/team/rate.html.twig', [
            'team' => $team,
            'form' => $form,
        ]);
    }
}
