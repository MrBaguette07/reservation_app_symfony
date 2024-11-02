<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ServiceController extends AbstractController
{
    #[Route('/services', name: 'service_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function listServices(EntityManagerInterface $entityManager): Response
    {
        $services = $entityManager->getRepository(Service::class)->findAll();

        return $this->render('service/list.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/services/new', name: 'add_service')]
    #[IsGranted('ROLE_ADMIN')]
    public function addService(Request $request, EntityManagerInterface $entityManager): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($service);
            $entityManager->flush();

            $this->addFlash('success', 'Le service a été ajouté avec succès.');

            return $this->redirectToRoute('service_list');
        }

        return $this->render('service/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/services/edit/{id}', name: 'edit_service')]
    #[IsGranted('ROLE_ADMIN')]
    public function editService(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $service = $entityManager->getRepository(Service::class)->find($id);

        if (!$service) {
            throw $this->createNotFoundException('Service non trouvé');
        }

        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le service a été modifié avec succès.');

            return $this->redirectToRoute('service_list');
        }

        return $this->render('service/edit.html.twig', [
            'form' => $form->createView(),
            'service' => $service,
        ]);
    }

    #[Route('/services/delete/{id}', name: 'delete_service', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteService(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $service = $entityManager->getRepository(Service::class)->find($id);

        if (!$service) {
            throw $this->createNotFoundException('Service non trouvé');
        }

        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->request->get('_token'))) {
            $entityManager->remove($service);
            $entityManager->flush();
            $this->addFlash('success', 'Le service a été supprimé avec succès.');
        }

        return $this->redirectToRoute('service_list');
    }
}