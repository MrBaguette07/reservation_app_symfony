<?php

namespace App\Service;

use App\Entity\Booking;
use Doctrine\ORM\EntityManagerInterface;

class AvailabilityChecker
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function isAvailable(Booking $booking): bool
    {
        $existingBooking = $this->entityManager->getRepository(Booking::class)
            ->findOneBy([
                'date' => $booking->getDate(),
                'service' => $booking->getService()
            ]);

        return $existingBooking === null;
    }
}