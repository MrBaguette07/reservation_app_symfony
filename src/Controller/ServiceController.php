<?php

namespace App\Controller;

use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServiceController extends AbstractController
{
    #[Route('/services', name: 'service_list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $services = $entityManager->getRepository(Service::class)->findAll();

        return $this->render('service/list.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/services/new', name: 'add_services')]
    public function addServices(EntityManagerInterface $entityManager): Response
    {        $servicesData = [
            ['name' => 'Coiffure', 'description' => 'Service de coiffure complet', 'price' => 50],
            ['name' => 'Massage', 'description' => 'Massage relaxant', 'price' => 70],
            ['name' => 'Spa', 'description' => 'Accès au spa pendant 2 heures', 'price' => 100],
        ];

        foreach ($servicesData as $data) {
            $service = new Service();
            $service->setName($data['name']);
            $service->setDescription($data['description']);
            $service->setPrice($data['price']);
        
            $entityManager->persist($service);
        }

        $entityManager->flush();

        return new Response('Les services ont été ajoutés avec succès.');
    }
}