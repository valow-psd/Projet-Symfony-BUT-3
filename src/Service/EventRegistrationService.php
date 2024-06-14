<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Registration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class EventRegistrationService
{
    private $entityManager;
    private $security;
    private $emailService;

    public function __construct(EntityManagerInterface $entityManager, Security $security, EmailService $emailService)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->emailService = $emailService;
    }

    public function register(Event $event): bool
    {
        $user = $this->security->getUser();

        if (!$user || $this->isEventFull($event)) {
            return false;
        }

        // Vérifier si l'utilisateur est déjà inscrit
        $existingRegistration = $this->entityManager->getRepository(Registration::class)->findOneBy([
            'user' => $user,
            'event' => $event,
        ]);

        if ($existingRegistration) {
            return false;
        }

        $registration = new Registration();
        $registration->setUser($user);
        $registration->setEvent($event);
        $registration->setCreatedAt(new \DateTime());

        $this->entityManager->persist($registration);
        
        // Flush to ensure the registration is persisted before modifying the event
        $this->entityManager->flush();

        // Décrémenter le nombre de participants maximum après la persistance de l'inscription
        $event->setMaxParticipants($event->getMaxParticipants() - 1);
        $this->entityManager->flush();

        $this->emailService->sendEmail(
            $user->getEmail(),
            'Event Registration Confirmation',
            'You have successfully registered for the event.'
        );

        return true;
    }

    public function unregister(Event $event): bool
    {
        $user = $this->security->getUser();

        if (!$user) {
            return false;
        }

        $registration = $this->entityManager->getRepository(Registration::class)->findOneBy([
            'user' => $user,
            'event' => $event,
        ]);

        if (!$registration) {
            return false;
        }

        $this->entityManager->remove($registration);
        
        // Flush to ensure the registration is removed before modifying the event
        $this->entityManager->flush();

        // Incrémenter le nombre de participants maximum après la suppression de l'inscription
        $event->setMaxParticipants($event->getMaxParticipants() + 1);
        $this->entityManager->flush();

        $this->emailService->sendEmail(
            $user->getEmail(),
            'Event Unregistration Confirmation',
            'You have successfully unregistered from the event.'
        );

        return true;
    }

    public function isEventFull(Event $event): bool
    {
        return $event->getMaxParticipants() <= 0;
    }
}
