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
use Dompdf\Dompdf;
use Dompdf\Options;

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
        // Get search and sort parameters from request
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'recent');
        
        // Create query builder
        $queryBuilder = $this->entityManager->getRepository(EventTeam::class)
            ->createQueryBuilder('et')
            ->select('et, e, t')
            ->leftJoin('et.event', 'e')
            ->leftJoin('et.team', 't');
            
        // Add search condition if search term is provided
        if (!empty($search)) {
            $queryBuilder->andWhere('et.title LIKE :search OR e.name LIKE :search OR t.TeamName LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        // Add sorting based on the selected option
        if ($sort === 'oldest_first') {
            $queryBuilder->orderBy('et.submissionId', 'ASC'); // Use submissionId instead
        } elseif ($sort === 'title_az') {
            $queryBuilder->orderBy('et.title', 'ASC');
        } elseif ($sort === 'title_za') {
            $queryBuilder->orderBy('et.title', 'DESC');
        } else {
            // Default: most recent first
            $queryBuilder->orderBy('et.submissionId', 'DESC'); // Use submissionId instead
        }
        
        // Get query
        $query = $queryBuilder->getQuery();

        // Paginate results with completely disabled sorting
        $eventTeams = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5,
            [
                'sortFieldWhitelist' => [], // Empty array means no fields are allowed for sorting
                'defaultSortFieldName' => null,
                'defaultSortDirection' => null,
                'sortDirectionParameterName' => 'none', // Use a non-standard parameter name
                'sortFieldParameterName' => 'none'      // Use a non-standard parameter name
            ]
        );

        return $this->render('front/event_team/index.html.twig', [
            'event_teams' => $eventTeams,
            'search' => $search,
            'sort' => $sort // Pass the sort parameter to the template
        ]);
    }
    
    #[Route('/new', name: 'app_event_team_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $eventTeam = new EventTeam();
        // Remove the setDate line since the entity doesn't have this field
        
        $form = $this->createForm(EventTeamType::class, $eventTeam);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Ensure event is set using the getter method
            if ($eventTeam->getEvent() === null) {
                $this->addFlash('error', 'Event must be selected.');
                return $this->redirectToRoute('app_event_team_new');
            }
            
            // Check if fileLink is provided
            $fileLink = $eventTeam->getFileLink();
            if (!$fileLink) {
                $this->addFlash('error', 'File link is required.');
                return $this->render('front/event_team/new.html.twig', [
                    'event_team' => $eventTeam,
                    'form' => $form,
                ]);
            }
            
            // Validate GitHub URL format
            if (!preg_match('/^https?:\/\/github\.com\/[^\/]+\/[^\/]+/', $fileLink)) {
                $this->addFlash('error', 'Invalid GitHub repository URL format. Please use https://github.com/username/repository');
                return $this->render('front/event_team/new.html.twig', [
                    'event_team' => $eventTeam,
                    'form' => $form,
                ]);
            }
            
            // Check if GitHub repository exists
            if (!$this->checkGitHubRepositoryExists($fileLink)) {
                $this->addFlash('error', 'The GitHub repository does not exist or is not accessible.');
                return $this->render('front/event_team/new.html.twig', [
                    'event_team' => $eventTeam,
                    'form' => $form,
                ]);
            }
            
            $this->addFlash('success', 'GitHub repository validated successfully!');
            $entityManager->persist($eventTeam);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_event_team_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('front/event_team/new.html.twig', [
            'event_team' => $eventTeam,
            'form' => $form,
        ]);
    }

    // Change this method
    #[Route('/{submission_id}', name: 'app_event_team_show', methods: ['GET'])]
    public function show(int $submission_id, EventTeamRepository $eventTeamRepository): Response
    {
        $eventTeam = $eventTeamRepository->find($submission_id);
        
        if (!$eventTeam) {
            $this->addFlash('error', 'Submission not found');
            return $this->redirectToRoute('app_event_team_index');
        }
        
        // Update the path to include the 'front' folder
        return $this->render('front/event_team/show.html.twig', [
            'event_team' => $eventTeam,
        ]);
    }

    // Also update this method
    #[Route('/{submission_id}/edit', name: 'app_event_team_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $submission_id, EventTeamRepository $eventTeamRepository, EntityManagerInterface $entityManager): Response
    {
        $eventTeam = $eventTeamRepository->find($submission_id);
        
        if (!$eventTeam) {
            $this->addFlash('error', 'Submission not found');
            return $this->redirectToRoute('app_event_team_index');
        }
        
        $form = $this->createForm(EventTeamType::class, $eventTeam);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Check if fileLink is provided
            $fileLink = $eventTeam->getFileLink();
            if (!$fileLink) {
                $this->addFlash('error', 'File link is required.');
                return $this->render('front/event_team/edit.html.twig', [
                    'event_team' => $eventTeam,
                    'form' => $form,
                ]);
            }
            
            // Validate GitHub URL format
            if (!preg_match('/^https?:\/\/github\.com\/[^\/]+\/[^\/]+/', $fileLink)) {
                $this->addFlash('error', 'Invalid GitHub repository URL format. Please use https://github.com/username/repository');
                return $this->render('front/event_team/edit.html.twig', [
                    'event_team' => $eventTeam,
                    'form' => $form,
                ]);
            }
            
            // Check if GitHub repository exists
            if (!$this->checkGitHubRepositoryExists($fileLink)) {
                $this->addFlash('error', 'The GitHub repository does not exist or is not accessible.');
                return $this->render('front/event_team/edit.html.twig', [
                    'event_team' => $eventTeam,
                    'form' => $form,
                ]);
            }
            
            $this->addFlash('success', 'GitHub repository validated successfully!');
            $entityManager->flush();
    
            return $this->redirectToRoute('app_event_team_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('front/event_team/edit.html.twig', [
            'event_team' => $eventTeam,
            'form' => $form,
        ]);
    }

    // And update this method too
    #[Route('/{submission_id}', name: 'app_event_team_delete', methods: ['POST'])]
    public function delete(Request $request, int $submission_id, EventTeamRepository $eventTeamRepository, EntityManagerInterface $entityManager): Response
    {
        $eventTeam = $eventTeamRepository->find($submission_id);
        
        if (!$eventTeam) {
            $this->addFlash('error', 'Submission not found');
            return $this->redirectToRoute('app_event_team_index');
        }
        
        // No CSRF token validation
        $entityManager->remove($eventTeam);
        $entityManager->flush();
    
        return $this->redirectToRoute('app_event_team_index', [], Response::HTTP_SEE_OTHER);
    }
    
    /**
     *
     * 
     * @param string 
     * @return bool 
     */
    private function checkGitHubRepositoryExists(string $repositoryUrl): bool
    {
        
        $pattern = '/github\.com\/([^\/]+)\/([^\/]+)/';
        if (!preg_match($pattern, $repositoryUrl, $matches)) {
            return false; 
        }
        
        $owner = $matches[1];
        $repo = $matches[2];
        
        
        $repo = rtrim($repo, '.git');
        
       
        $apiUrl = "https://api.github.com/repos/{$owner}/{$repo}";
        
        
        $ch = curl_init($apiUrl);
        
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'EventTeam-App'); // GitHub requires a user agent
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request only
        
        // Execute cURL request
        curl_exec($ch);
        
        // Get HTTP response code
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Close cURL session
        curl_close($ch);
        
        // Repository exists if HTTP code is 200 (OK)
        return $httpCode === 200;
    }
    
    /**
     * Test route for checking if a GitHub repository exists
     */
    #[Route('/test-github-repo', name: 'app_event_team_test_github_repo', methods: ['GET'])]
    public function testGitHubRepo(Request $request): Response
    {
        $repoUrl = $request->query->get('url');
        
        if (!$repoUrl) {
            return $this->json([
                'error' => 'Missing URL parameter',
                'usage' => 'Add ?url=https://github.com/username/repository to test'
            ], 400);
        }
        
        $exists = $this->checkGitHubRepositoryExists($repoUrl);
        
        return $this->json([
            'repository_url' => $repoUrl,
            'exists' => $exists,
            'status' => $exists ? 'Repository exists and is accessible' : 'Repository does not exist or is not accessible'
        ]);
    }
    
    
    #[Route('/{submission_id}/certificate', name: 'app_event_team_certificate', methods: ['GET'])]
    public function generateCertificate(int $submission_id, EventTeamRepository $eventTeamRepository): Response
    {
        $eventTeam = $eventTeamRepository->find($submission_id);
        
        if (!$eventTeam) {
            $this->addFlash('error', 'Submission not found');
            return $this->redirectToRoute('app_event_team_index');
        }
        
        // Get related entities
        $event = $eventTeam->getEvent();
        $team = $eventTeam->getTeam();
        
        if (!$event || !$team) {
            $this->addFlash('error', 'Event or Team information is missing');
            return $this->redirectToRoute('app_event_team_index');
        }
        
        // Render the certificate template
        return $this->render('front/event_team/certificate.html.twig', [
            'event_team' => $eventTeam,
            'event' => $event,
            'team' => $team,
            'date' => new \DateTime(),
        ]);
    }
    
    /**
     * Generate a PDF certificate for a team's event submission using Dompdf
     */
    #[Route('/{submission_id}/certificate/pdf', name: 'app_event_team_certificate_pdf', methods: ['GET'])]
    public function generateCertificatePdf(int $submission_id, EventTeamRepository $eventTeamRepository): Response
    {
        $eventTeam = $eventTeamRepository->find($submission_id);
        
        if (!$eventTeam) {
            $this->addFlash('error', 'Submission not found');
            return $this->redirectToRoute('app_event_team_index');
        }
        
        // Get related entities
        $event = $eventTeam->getEvent();
        $team = $eventTeam->getTeam();
        
        if (!$event || !$team) {
            $this->addFlash('error', 'Event or Team information is missing');
            return $this->redirectToRoute('app_event_team_index');
        }
        
        // Configure Dompdf options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        // Instantiate Dompdf
        $dompdf = new Dompdf($options);
        
        // Generate HTML content for the certificate
        $html = $this->renderView('front/event_team/certificate_pdf.html.twig', [
            'event_team' => $eventTeam,
            'event' => $event,
            'team' => $team,
            'date' => new \DateTime(),
        ]);
        
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // Set paper size and orientation
        $dompdf->setPaper('A4', 'landscape');
        
        // Render the PDF
        $dompdf->render();
        
        // Generate a filename for the PDF
        $filename = sprintf('certificate-%s-%s-%s.pdf', 
            $team->getTeamName(), 
            $event->getName(), 
            (new \DateTime())->format('Y-m-d')
        );
        
        // Output the generated PDF (inline = 0 to download, 1 to preview in browser)
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            ]
        );
    }
    
    /**
     * Rate a team's event submission
     */
    #[Route('/{submission_id}/rate', name: 'app_event_team_rate', methods: ['POST'])]
    public function rateSubmission(Request $request, int $submission_id, EventTeamRepository $eventTeamRepository, EntityManagerInterface $entityManager): Response
    {
        $eventTeam = $eventTeamRepository->find($submission_id);
        
        if (!$eventTeam) {
            $this->addFlash('error', 'Submission not found');
            return $this->redirectToRoute('app_event_team_index');
        }
        
        // Get rating data from request
        $rating = (int) $request->request->get('rating', 0);
        $comment = $request->request->get('comment', '');
        
        // Validate rating
        if ($rating < 1 || $rating > 5) {
            $this->addFlash('error', 'Invalid rating value. Please select 1-5 stars.');
            return $this->redirectToRoute('app_event_team_show', ['submission_id' => $submission_id]);
        }
        
        // Store the rating in the database
        // Note: You'll need to create a Rating entity or add rating fields to EventTeam
        // For now, we'll just show a success message
        
        $this->addFlash('success', 'Thank you for rating this submission!');
        
        // Redirect back to the submission page
        return $this->redirectToRoute('app_event_team_show', ['submission_id' => $submission_id]);
    }
}
