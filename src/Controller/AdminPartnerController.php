<?php

namespace App\Controller;

use App\Entity\Partner;
use App\Form\PartnerType;
use App\Repository\PartnerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/partner')]
final class AdminPartnerController extends AbstractController
{
    #[Route(name: 'app_admin_partner_index', methods: ['GET'])]
    public function index(PartnerRepository $partnerRepository): Response
    {
        return $this->render('partner/indexBack.html.twig', [
            'partners' => $partnerRepository->findAll(),
        ]);
    }


    #[Route('/{partnerId}', name: 'app_admin_partner_show', methods: ['GET'])]
    public function show(Partner $partner): Response
    {
        return $this->render('partner/showBack.html.twig', [
            'partner' => $partner,
        ]);
    }
}
