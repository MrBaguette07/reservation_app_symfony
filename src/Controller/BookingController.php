<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Service\AvailabilityChecker;
use App\Entity\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class BookingController extends AbstractController
{
    #[Route('/booking', name: 'booking')]
    public function book(Request $request, EntityManagerInterface $entityManager): Response
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($booking);
            $entityManager->flush();

            return $this->redirectToRoute('booking_confirmation');
        }

        return $this->render('booking/book.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/services', name: 'service_list')]
    public function listServices(EntityManagerInterface $entityManager): Response
    {
        $services = $entityManager->getRepository(Service::class)->findAll();

        return $this->render('booking/service_list.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/booking/new', name: 'new_booking')]
    public function newBooking(Request $request, EntityManagerInterface $entityManager, AvailabilityChecker $availabilityChecker): Response
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$availabilityChecker->isAvailable($booking)) {
                $this->addFlash('error', 'Le créneau choisi est déjà réservé. Veuillez choisir un autre horaire.');
            } else {
                $entityManager->persist($booking);
                $entityManager->flush();

                $this->addFlash('success', 'Votre réservation a été confirmée !');
                return $this->redirectToRoute('booking_confirmation');
            }
        }

        return $this->render('booking/new_booking.html.twig', [
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


    #[Route('/booking/cancel/{id}', name: 'cancel_booking')]
    public function cancelBooking(int $id, EntityManagerInterface $entityManager): Response
    {
        $booking = $entityManager->getRepository(Booking::class)->find($id);

        if ($booking) {
            $entityManager->remove($booking);
            $entityManager->flush();
            $this->addFlash('success', 'Réservation annulée.');
        } else {
            $this->addFlash('error', 'Réservation introuvable.');
        }

        return $this->redirectToRoute('service_list');
    }


}