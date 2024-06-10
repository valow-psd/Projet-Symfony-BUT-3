<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/event/new', name: 'event_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_success');
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
    public function list(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        $queryBuilder = $entityManager->getRepository(Event::class)->createQueryBuilder('e')
            ->where('e.isPublic = true OR (e.isPublic = false AND e.createdBy = :user)')
            ->setParameter('user', $this->getUser());

        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('event/list.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/event/{id}', name: 'event_detail')]
    #[IsGranted('view', subject: 'event')]
    public function detail(Event $event): Response
    {
        return $this->render('event/detail.html.twig', [
            'event' => $event,
        ]);
    }
}
