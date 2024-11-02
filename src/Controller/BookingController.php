<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Service\AvailabilityChecker;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class BookingController extends AbstractController
{
    #[Route('/booking', name: 'user_bookings')]
    #[IsGranted('ROLE_USER')]
    public function listBookings(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $bookings = $entityManager->getRepository(Booking::class)
            ->findBy(['user' => $user]);

        return $this->render('booking/list.html.twig', [
            'bookings' => $bookings,
        ]);
    }

    #[Route('/booking/new', name: 'new_booking')]
    #[IsGranted('ROLE_USER')]
    public function newBooking(Request $request, EntityManagerInterface $entityManager, AvailabilityChecker $availabilityChecker): Response
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $booking->setUser($this->getUser());

            if (!$availabilityChecker->isAvailable($booking)) {
                $this->addFlash('error', 'Le créneau sélectionné est déjà réservé. Veuillez en choisir un autre.');
            } else {
                $entityManager->persist($booking);
                $entityManager->flush();

                $this->addFlash('success', 'Votre réservation a été créée avec succès !');
                return $this->redirectToRoute('user_bookings');
            }
        }

        return $this->render('booking/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/booking/confirm/{id}', name: 'confirm_booking')]
    public function confirmBooking(int $id, EntityManagerInterface $entityManager): Response
    {
        $booking = $entityManager->getRepository(Booking::class)->find($id);

        if ($booking) {
            $booking->setStatus('confirmed');
            $entityManager->flush();
            $this->addFlash('success', 'Réservation confirmée.');
        } else {
            $this->addFlash('error', 'Réservation introuvable.');
        }

        return $this->redirectToRoute('service_list');
    }


    #[Route('/booking/cancel/{id}', name: 'cancel_booking', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancelBooking(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $booking = $entityManager->getRepository(Booking::class)->find($id);

        if (!$booking || $booking->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Réservation introuvable ou accès non autorisé.');
            return $this->redirectToRoute('user_bookings');
        }

        if (!$this->isCsrfTokenValid('cancel' . $booking->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('user_bookings');
        }

        $entityManager->remove($booking);
        $entityManager->flush();

        $this->addFlash('success', 'Votre réservation a été annulée avec succès.');
        return $this->redirectToRoute('user_bookings');
    }

    #[Route('/admin/bookings', name: 'admin_bookings')]
    #[IsGranted('ROLE_ADMIN')]
    public function listAllBookings(EntityManagerInterface $entityManager): Response
    {
        $bookings = $entityManager->getRepository(Booking::class)->findAll();

        return $this->render('booking/admin_list.html.twig', [
            'bookings' => $bookings,
        ]);
    }



}