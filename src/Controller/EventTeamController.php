<?php

namespace App\Controller;

use App\Entity\EventTeam;
use App\Form\EventTeamType;
use App\Repository\EventTeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/event/team')]
class EventTeamController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_event_team_index', methods: ['GET'])]
public function index(Request $request, PaginatorInterface $paginator): Response
{
    $query = $this->entityManager->getRepository(EventTeam::class)
        ->createQueryBuilder('et')
        ->select('et, e, t')
        ->leftJoin('et.event', 'e')
        ->leftJoin('et.team', 't')
        ->where('t.id IS NOT NULL') // Only include records with existing teams
        ->orderBy('et.submissionId', 'DESC')
        ->getQuery();

    $eventTeams = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        5,
        [
            'pageParameterName' => 'page',
            'sortFieldParameterName' => 'sort',
            'sortDirectionParameterName' => 'direction',
            'defaultSortFieldName' => 'et.submissionId',
            'defaultSortDirection' => 'DESC',
        ]
    );

    return $this->render('front/event_team/index.html.twig', [
        'event_teams' => $eventTeams,
    ]);
}
    
    #[Route('/new', name: 'app_event_team_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $eventTeam = new EventTeam();
        $eventTeam->setSubmissionDate(new \DateTime());
        
        $form = $this->createForm(EventTeamType::class, $eventTeam);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Ensure event is set using the getter method
            if ($eventTeam->getEvent() === null) {
                $this->addFlash('error', 'Event must be selected.');
                return $this->redirectToRoute('app_event_team_new');
            }
    
            $entityManager->persist($eventTeam);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_event_team_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('front/event_team/new.html.twig', [
            'event_team' => $eventTeam,
            'form' => $form,
        ]);
    }

    #[Route('/{submissionId}', name: 'app_event_team_show', methods: ['GET'])]
    public function show(EventTeam $eventTeam): Response
    {
        // Update the path to include the 'front' folder
        return $this->render('front/event_team/show.html.twig', [
            'event_team' => $eventTeam,
        ]);
    }

    #[Route('/{submissionId}/edit', name: 'app_event_team_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EventTeam $eventTeam, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventTeamType::class, $eventTeam);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            return $this->redirectToRoute('app_event_team_index', [], Response::HTTP_SEE_OTHER);
        }
    
        // Update the path to include the 'front' folder
        return $this->render('front/event_team/edit.html.twig', [
            'event_team' => $eventTeam,
            'form' => $form,
        ]);
    }

    #[Route('/{submissionId}', name: 'app_event_team_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, EventTeam $eventTeam, EntityManagerInterface $entityManager): Response
    {
        // No CSRF token validation
        $entityManager->remove($eventTeam);
        $entityManager->flush();
    
        return $this->redirectToRoute('app_event_team_index', [], Response::HTTP_SEE_OTHER);
    }
}
