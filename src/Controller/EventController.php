<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Registration;
use App\Form\EventType;
use App\Form\EventFilterType;
use App\Service\EmailService;
use App\Service\EventRegistrationService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class EventController extends AbstractController
{

    private $stripePublicKey;
    private $eventRegistrationService;
    private $emailService;

    public function __construct(string $stripePublicKey, EventRegistrationService $eventRegistrationService, EmailService $emailService)
    {
        $this->stripePublicKey = $stripePublicKey;
        $this->eventRegistrationService = $eventRegistrationService;
        $this->emailService = $emailService;
    }

    #[Route('/event/new', name: 'event_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();
            $event->setCreatedBy($user);

            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_detail', ['id' => $event->getId()]);
        }

        return $this->render('event/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/event/success', name: 'event_success')]
    public function success(): Response
    {
        return $this->render('event/success.html.twig');
    }

    #[Route('/events', name: 'event_list')]
    public function list(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        $form = $this->createForm(EventFilterType::class);
        $form->handleRequest($request);

        $queryBuilder = $entityManager->getRepository(Event::class)->createQueryBuilder('e')
            ->where('e.isPublic = true OR (e.isPublic = false AND e.createdBy = :user)')
            ->setParameter('user', $this->getUser());

        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();

            if (!empty($filters['title'])) {
                $queryBuilder->andWhere('e.title LIKE :title')
                    ->setParameter('title', '%' . $filters['title'] . '%');
            }

            if (!empty($filters['date'])) {
                $startDate = (new \DateTime($filters['date']->format('Y-m-d')))->setTime(0, 0, 0);
                $endDate = (new \DateTime($filters['date']->format('Y-m-d')))->setTime(23, 59, 59);
                $queryBuilder->andWhere('e.date BETWEEN :startDate AND :endDate')
                    ->setParameter('startDate', $startDate)
                    ->setParameter('endDate', $endDate);
            }

            if (!empty($filters['maxParticipants'])) {
                $queryBuilder->andWhere('e.maxParticipants = :maxParticipants')
                    ->setParameter('maxParticipants', $filters['maxParticipants']);
            }
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('event/list.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('view', subject: 'event')]
    #[Route('/event/{id}', name: 'event_detail', methods: ['GET'])]
    public function detail(Event $event, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = $security->getUser();
        $isRegistered = false;

        if ($user) {
            $registration = $entityManager->getRepository(Registration::class)->findOneBy([
                'user' => $user,
                'event' => $event,
            ]);

            $isRegistered = $registration !== null;
        }

        $isFull = $event->getRegistrations()->count() >= $event->getMaxParticipants();

        return $this->render('event/detail.html.twig', [
            'event' => $event,
            'isRegistered' => $isRegistered,
            'isFull' => $isFull,
        ]);
    }

    #[Route('/event/edit/{id}', name: 'event_edit')]
    #[IsGranted('edit', 'event')]
    public function edit(Event $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('event_detail', ['id' => $event->getId()]);
        }

        return $this->render('event/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/event/delete/{id}', name: 'event_delete')]
    #[IsGranted('delete', 'event')]
    public function delete(Event $event, EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('delete', $event);

        if ($request->isMethod('POST')) {
            $entityManager->remove($event);
            $entityManager->flush();
            $this->addFlash('success', 'Event deleted successfully.');

            return $this->redirectToRoute('event_list');
        }

        return $this->render('event/delete.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/events/subscribed', name: 'event_subscribed')]
    public function subscribed(EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('error', 'You must be logged in to view your subscribed events.');
            return $this->redirectToRoute('app_login');
        }

        $registrations = $entityManager->getRepository(Registration::class)->findBy(['user' => $user]);

        return $this->render('event/subscribed.html.twig', [
            'registrations' => $registrations,
        ]);
    }

    #[Route('/event/pay/{id}', name: 'event_pay')]
    public function pay(Event $event, StripeService $stripeService): Response
    {
        if (!$event->getIsPaid() || $event->getPrice() <= 0) {
            throw $this->createNotFoundException('This event is not a paid event.');
        }

        $paymentIntent = $stripeService->createPaymentIntent($event->getPrice(), 'usd');

        return $this->render('event/pay.html.twig', [
            'event' => $event,
            'clientSecret' => $paymentIntent->client_secret,
            'publicKey' => $this->stripePublicKey,
        ]);
    }

    #[Route('/event/confirm/{id}', name: 'event_confirm', methods: ['POST'])]
    public function confirmPayment(Event $event, Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('error', 'You must be logged in to register for an event.');
            return $this->redirectToRoute('app_login');
        }

        $this->eventRegistrationService->markAsPaid($user, $event);

        $this->addFlash('success', 'Your payment was successful and you have been registered for the event.');

        return $this->redirectToRoute('event_detail', ['id' => $event->getId()]);
    }
}
