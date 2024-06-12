<?php

namespace App\Controller;

use App\Entity\Registration;
use App\Entity\User;
use App\Entity\Event;
use App\Service\EmailService;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\SecurityControllerAuthenticator;

class RegistrationController extends AbstractController
{

    private $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    #[Route('/registration', name: 'app_registration')]
    public function index(): Response
    {
        return $this->render('registration/index.html.twig', [
            'controller_name' => 'RegistrationController',
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, UserAuthenticatorInterface $authenticator, SecurityControllerAuthenticator $loginAuthenticator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the password
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $user->getPlainPassword()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // Authenticate the user
            return $authenticator->authenticateUser(
                $user,
                $loginAuthenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/event/register/{id}', name: 'event_register', methods: ['POST'])]
    public function registerEvent(Event $event, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('error', 'You must be logged in to register for an event.');
            return $this->redirectToRoute('app_login');
        }

        if ($event->getRegistrations()->count() >= $event->getMaxParticipants()) {
            $this->addFlash('error', 'This event is already full.');
            return $this->redirectToRoute('event_detail', ['id' => $event->getId()]);
        }

        $registration = new Registration();
        $registration->setUser($user);
        $registration->setEvent($event);
        $registration->setCreatedAt(new \DateTime());

        $entityManager->persist($registration);
        $entityManager->flush();

        $this->emailService->sendEmail(
            $user->getEmail(),
            'Event Registration Confirmation',
            'You have successfully registered for the event.'
        );

        $this->addFlash('success', 'You have successfully registered for the event.');

        return $this->redirectToRoute('event_detail', ['id' => $event->getId()]);
    }

    #[Route('/event/unregister/{id}', name: 'event_unregister', methods: ['POST'])]
    public function unregisterEvent(Event $event, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('error', 'You must be logged in to unregister from an event.');
            return $this->redirectToRoute('app_login');
        }

        $registration = $entityManager->getRepository(Registration::class)->findOneBy([
            'user' => $user,
            'event' => $event,
        ]);

        if (!$registration) {
            $this->addFlash('error', 'You are not registered for this event.');
            return $this->redirectToRoute('event_detail', ['id' => $event->getId()]);
        }

        $entityManager->remove($registration);
        $entityManager->flush();

        $this->emailService->sendEmail(
            $user->getEmail(),
            'Event Unregistration Confirmation',
            'You have successfully unregistered from the event.'
        );

        $this->addFlash('success', 'You have successfully unregistered from the event.');

        return $this->redirectToRoute('event_detail', ['id' => $event->getId()]);
    }
}
