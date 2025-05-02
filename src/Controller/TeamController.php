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
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
        // Get search and sort parameters from request
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'recent');
        
        // Create query builder
        $queryBuilder = $this->entityManager->getRepository(Team::class)
            ->createQueryBuilder('t')
            ->leftJoin('t.event', 'e');
            
        // Add search condition if search term is provided
        if (!empty($search)) {
            $queryBuilder->andWhere('t.TeamName LIKE :search OR e.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        // Add sorting based on the selected option
        if ($sort === 'highest_score') {
            // Use a different approach to avoid entity field issues
            $queryBuilder->addOrderBy('t.id', 'DESC');
        } elseif ($sort === 'lowest_score') {
            // Use a different approach to avoid entity field issues
            $queryBuilder->addOrderBy('t.id', 'ASC');
        } elseif ($sort === 'best_rank') {
            // Use a different approach to avoid entity field issues
            $queryBuilder->addOrderBy('t.id', 'ASC');
        } elseif ($sort === 'name_az') {
            $queryBuilder->orderBy('t.TeamName', 'ASC');
        } elseif ($sort === 'name_za') {
            $queryBuilder->orderBy('t.TeamName', 'DESC');
        } else {
            // Default: most recent first (by ID)
            $queryBuilder->orderBy('t.id', 'DESC');
        }
        
        // Get query
        $query = $queryBuilder->getQuery();

        // Paginate results with sorting disabled in KnpPaginator
        $teams = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5, // 5 items per page
            [
                'sortFieldWhitelist' => [], // Empty array means no fields are allowed for sorting
                'defaultSortFieldName' => null,
                'defaultSortDirection' => null,
                'sortDirectionParameterName' => 'none', // Use a non-standard parameter name
                'sortFieldParameterName' => 'none'      // Use a non-standard parameter name
            ]
        );

        return $this->render('front/team/index.html.twig', [
            'teams' => $teams,
            'search' => $search,
            'sort' => $sort // Pass the sort parameter to the template
        ]);
    }

    #[Route('/new', name: 'app_team_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $team = new Team();
        // Set default values - use 0 instead of null since the database doesn't allow nulls
        $team->setScore(0); // This is correct as it uses the setter method
        $team->setRank(0);  // This is correct as it uses the setter method
        
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
    
    // Add this method to your TeamController class
    
    #[Route('/{id}/rate', name: 'app_team_rate', methods: ['GET', 'POST'])]
    public function rate(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        // Create a simple form with score and rank fields
        $form = $this->createFormBuilder($team)
            ->add('score', NumberType::class, [ // Changed from Score to score
                'label' => 'Score',
                'property_path' => 'Score', // Map to the actual Score property
                'attr' => ['min' => 0, 'max' => 100, 'step' => 0.1]
            ])
            ->add('rank', NumberType::class, [ // Changed from Rank to rank
                'label' => 'Rank',
                'property_path' => 'Rank', // Map to the actual Rank property
                'attr' => ['min' => 1]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Update Rating',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ])
            ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Team rating updated successfully!');
            return $this->redirectToRoute('app_team_index');
        }
        
        return $this->render('front/team/rate.html.twig', [
            'team' => $team,
            'form' => $form,
        ]);
    }
}
