<?php

namespace App\Controller;

use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/admin')]  // Change the base route to handle both dashboard and team routes
class AdminTeamController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('back/dashboard/index.html.twig');
    }

    #[Route('/team', name: 'app_admin_team_index')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $query = $this->entityManager->getRepository(Team::class)
            ->createQueryBuilder('t')
            ->orderBy('t.id', 'DESC')
            ->getQuery();

        $teams = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5  // Changed from 10 to 5 items per page
        );

        return $this->render('back/team/index.html.twig', [
            'teams' => $teams,
        ]);
    }

    #[Route('/team/delete/{id}', name: 'app_admin_team_delete')]
    public function delete(int $id): Response
    {
        $team = $this->entityManager->getRepository(Team::class)->find($id);
        
        if (!$team) {
            throw $this->createNotFoundException('Team not found');
        }
        
        $this->entityManager->remove($team);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Team deleted successfully');
        
        return $this->redirectToRoute('app_admin_team_index');
    }
}