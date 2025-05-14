<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class test extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('basePlanner.html.twig');
    }
    #[Route('/admin', name: 'app_back')]
    public function index2(): Response
    {
        return $this->render('back.html.twig');
    }
}

