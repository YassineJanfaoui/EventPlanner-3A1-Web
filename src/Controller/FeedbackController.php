<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Form\FeedbackType;
use App\Repository\FeedbackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/feedback')]
class FeedbackController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private const API_URL = "https://api-inference.huggingface.co/models/distilbert-base-uncased-finetuned-sst-2-english";
    private const API_KEY = "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";
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_feedback_index', methods: ['GET'])]
    public function index(Request $request, FeedbackRepository $feedbackRepository, PaginatorInterface $paginator): Response
    {
        // Get search parameter from request
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', '');
        
        // Create a simple query builder
        $queryBuilder = $feedbackRepository->createQueryBuilder('f');
        
        // Add search condition if search term is provided
        if (!empty($search)) {
            $queryBuilder->andWhere('f.comment LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        // Add sorting based on the selected option
        if ($sort === 'highest_rating') {
            // Simply sort by the rate field in descending order
            $queryBuilder->orderBy('f.rate', 'DESC');
        } elseif ($sort === 'positive_first') {
            // Use a simpler approach for sentiment sorting
            $queryBuilder
                ->orderBy('f.sentiment', 'DESC') // 'positive' will come before 'negative' alphabetically
                ->addOrderBy('f.feedbackId', 'DESC');
        } else {
            // Default sorting
            $queryBuilder->orderBy('f.feedbackId', 'DESC');
        }
        
        // Get query
        $query = $queryBuilder->getQuery();
    
        // Paginate results with completely disabled sorting
        $feedback = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            3, // 5 items per page
            [
                'sortFieldAllowList' => [], // Empty array means no fields are allowed for sorting
                'defaultSortFieldName' => null,
                'defaultSortDirection' => null,
                'sortDirectionParameterName' => 'none', // Use a non-standard parameter name
                'sortFieldParameterName' => 'none'      // Use a non-standard parameter name
            ]
        );
    
        return $this->render('front/feedback/index.html.twig', [
            'feedback' => $feedback,
            'feedbacks' => $feedback, // Keep both variables for compatibility
            'search' => $search,
            'sort' => $sort // Pass the sort parameter to the template
        ]);
    }

    #[Route('/new', name: 'app_feedback_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $feedback = new Feedback();
        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Analyze sentiment before persisting
            $content = $feedback->getComment();
            if ($content) {
                $sentiment = $this->analyzeSentiment($content);
                $feedback->setSentiment($sentiment);
                
                // Add a flash message about the sentiment analysis
                $sentimentMessage = ucfirst($sentiment) === 'Positive' 
                    ? 'Great! Your feedback has a positive tone.' 
                    : (ucfirst($sentiment) === 'Negative' 
                        ? 'We appreciate your honest feedback, even though it has a negative tone.' 
                        : 'Thank you for your neutral feedback.');
                
                $this->addFlash('info', $sentimentMessage);
            }
            
            $entityManager->persist($feedback);
            $entityManager->flush();

            return $this->redirectToRoute('app_feedback_index', [], Response::HTTP_SEE_OTHER);
        }

        // If the form was submitted but had validation errors, we might already have sentiment
        if ($form->isSubmitted() && $feedback->getComment()) {
            $sentiment = $this->analyzeSentiment($feedback->getComment());
            $feedback->setSentiment($sentiment);
        }

        return $this->render('front/feedback/new.html.twig', [
            'feedback' => $feedback,
            'form' => $form,
        ]);
    }

    #[Route('/{feedbackId}', name: 'app_feedback_show', methods: ['GET'])]
    public function show(Feedback $feedback): Response
    {
        return $this->render('front/feedback/show.html.twig', [
            'feedback' => $feedback,
        ]);
    }

    #[Route('/{feedbackId}/edit', name: 'app_feedback_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Feedback $feedback, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Re-analyze sentiment when content is updated
            $content = $feedback->getComment(); // Changed from getContent() to getComment()
            if ($content) {
                $sentiment = $this->analyzeSentiment($content);
                $feedback->setSentiment($sentiment);
            }
            
            $entityManager->flush();

            return $this->redirectToRoute('app_feedback_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/feedback/edit.html.twig', [
            'feedback' => $feedback,
            'form' => $form,
        ]);
    }

    #[Route('/{feedbackId}', name: 'app_feedback_delete', methods: ['POST'])]
    public function delete(Request $request, Feedback $feedback, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$feedback->getFeedbackId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($feedback);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_feedback_index', [], Response::HTTP_SEE_OTHER);
    }
    
    /**
     * Analyze sentiment of text using Hugging Face API
     * 
     * @param string $text The text to analyze
     * @return string The sentiment label (positive, negative, or neutral)
     */
    private function analyzeSentiment(string $text): string
    {
        if (empty(trim($text))) {
            return 'neutral';
        }
        
        // Initialize cURL session
        $ch = curl_init(self::API_URL);
        
        // Prepare the request payload
        $payload = json_encode(['inputs' => $text]);
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . self::API_KEY,
            'Content-Type: application/json'
        ]);
        
        // Execute cURL request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Check for errors
        if (curl_errno($ch) || $httpCode !== 200) {
            curl_close($ch);
            return 'neutral';
        }
        
        // Close cURL session
        curl_close($ch);
        
        // Parse the response
        $result = json_decode($response, true);
        
        // Extract sentiment label
        if (is_array($result) && !empty($result) && isset($result[0][0]['label'])) {
            return strtolower($result[0][0]['label']);
        }
        
        return 'neutral';
    }
    
    /**
     * Test route for sentiment analysis
     */
    #[Route('/test-sentiment', name: 'app_feedback_test_sentiment', methods: ['GET'])]
    public function testSentiment(Request $request): Response
    {
        $text = $request->query->get('text', '');
        $sentiment = $this->analyzeSentiment($text);
        
        return $this->json([
            'text' => $text,
            'sentiment' => $sentiment
        ]);
    }
    
    // Add this new route for AJAX sentiment analysis
    /**
     * Update sentiment display with appropriate styling
     * 
     * @param string $sentiment The sentiment label
     * @return array HTML and class information for the sentiment display
     */
    private function updateSentimentDisplay(string $sentiment): array
    {
        $sentiment = strtolower($sentiment);
        
        // Determine the appropriate CSS class based on sentiment
        $cssClass = match($sentiment) {
            'positive' => 'bg-success',
            'negative' => 'bg-danger',
            default => 'bg-secondary'
        };
        
        // Create the HTML for the badge
        $html = sprintf(
            '<span class="badge %s" style="font-size: 1rem; padding: 6px 10px;">Sentiment: %s</span>',
            $cssClass,
            strtoupper($sentiment)
        );
        
        return [
            'html' => $html,
            'sentiment' => $sentiment,
            'class' => $cssClass
        ];
    }
    
    /**
     * Test route for sentiment analysis
     */
    #[Route('/analyze-sentiment', name: 'app_feedback_analyze_sentiment', methods: ['POST'])]
    public function analyzeCommentSentiment(Request $request): Response
    {
        $text = $request->request->get('text', '');
        $sentiment = $this->analyzeSentiment($text);
        
        // Use the updateSentimentDisplay method to get formatted output
        $displayData = $this->updateSentimentDisplay($sentiment);
        
        return $this->json([
            'sentiment' => $sentiment,
            'badge' => $displayData['html'],
            'class' => $displayData['class']
        ]);
    }
    
    /**
     * Generate HTML for sentiment badge
     */
    private function getSentimentBadgeHtml(string $sentiment): string
    {
        $colorClass = match($sentiment) {
            'positive' => 'success',
            'negative' => 'danger',
            default => 'secondary'
        };
        
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $colorClass,
            ucfirst($sentiment)
        );
    }
}
