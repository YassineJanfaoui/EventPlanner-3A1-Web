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
    // #[Route('/{partnerId}/edit', name: 'app_partner_edit_admin', methods: ['GET', 'POST'])]
    // public function edit(Request $request, Partner $partner, EntityManagerInterface $entityManager): Response
    // {
    //     $form = $this->createForm(PartnerType::class, $partner);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager->flush();

    //         return $this->redirectToRoute('app_partner_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->render('partner/edit.html.twig', [
    //         'partner' => $partner,
    //         'form' => $form,
    //     ]);
    // }
    #[Route('/{partnerId}', name: 'app_partner_delete_admin', methods: ['POST'])]
    public function delete(Request $request, Partner $partner, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$partner->getPartnerId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($partner);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_partner_index', [], Response::HTTP_SEE_OTHER);
    }
}
