<?php

namespace App\Controller;

use App\Entity\Equipment;
use App\Form\EquipmentType;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminEquipmentController extends AbstractController
{
    #[Route('/admin/equipment', name: 'app_admin_equipment', methods: ['GET'])]
    public function index(EquipmentRepository $equipmentRepository): Response
    {
        return $this->render('equipment/indexBack.html.twig', [
            'equipments' => $equipmentRepository->findAll(),
        ]);
    }

    #[Route('/admin/equipment/{EquipmentId}', name: 'app_admin_equipment_show', methods: ['GET'])]
    public function show(Equipment $equipment): Response
    {
        return $this->render('equipment/showBack.html.twig', [
            'equipment' => $equipment,
        ]);
    }
}
