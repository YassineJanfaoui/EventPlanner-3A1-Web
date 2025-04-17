<?php

namespace App\Controller;

use App\Entity\Feedback;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/admin/feedback')]
class AdminFeedbackController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_admin_feedback_index')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        // Simplified query without ordering to avoid field errors
        $query = $this->entityManager->getRepository(Feedback::class)
            ->createQueryBuilder('f')
            ->getQuery();

        $feedbacks = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5  // Changed from 10 to 5 items per page
        );

        return $this->render('back/feedback/index.html.twig', [
            'feedbacks' => $feedbacks,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_feedback_delete')]
    public function delete(int $id): Response
    {
        $feedback = $this->entityManager->getRepository(Feedback::class)->findOneBy([
            'feedbackId' => $id
        ]);
        
        if (!$feedback) {
            throw $this->createNotFoundException('Feedback not found');
        }
        
        $this->entityManager->remove($feedback);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Feedback deleted successfully');
        
        return $this->redirectToRoute('app_admin_feedback_index');
    }
}