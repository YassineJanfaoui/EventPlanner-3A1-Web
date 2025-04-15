<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin_index')]
    public function index(): Response
    {
        // Redirect to the dashboard
        return $this->redirectToRoute('app_admin_dashboard');
    }

    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('back/dashboard.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}