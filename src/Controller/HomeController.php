<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Registration;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();

        // Récupérer les 5 derniers événements
        $events = $entityManager->getRepository(Event::class)->findBy([], ['date' => 'DESC'], 5);

        // Récupérer les inscriptions de l'utilisateur
        $registrations = [];
        if ($user) {
            $registrations = $entityManager->getRepository(Registration::class)->findBy(['user' => $user]);
        }

        // Créer un tableau associatif pour vérifier l'inscription de l'utilisateur
        $userEvents = [];
        foreach ($registrations as $registration) {
            $userEvents[$registration->getEvent()->getId()] = true;
        }

        // Ajouter la propriété `isFull` à chaque événement
        foreach ($events as $event) {
            $event->isFull = $event->getMaxParticipants() <= count($event->getRegistrations());
        }

        return $this->render('home.html.twig', [
            'events' => $events,
            'userEvents' => $userEvents,
        ]);
    }
}
