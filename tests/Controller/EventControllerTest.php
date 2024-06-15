<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EventControllerTest extends WebTestCase
{
    public function testNewEventPageIsAccessible()
    {
        $client = static::createClient();
        $client->request('GET', '/event/new');

        $this->assertResponseIsSuccessful();
//        $this->assertSelectorTextContains('h2', 'Create a new Event');
    }

    public function testCreateNewEvent()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/event/new');

        $form = $crawler->selectButton('Save')->form([
            'event[title]' => 'New Event',
            'event[description]' => 'Event description.',
            'event[date]' => '2024-12-31 12:00',
            'event[maxParticipants]' => 100,
            'event[isPublic]' => true,
        ]);

        $client->submit($form);
        $this->assertResponseRedirects('/event/37'); // Assuming the new event ID is 1

        $client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Lazer Dim 700 vs Lilkris3000');
    }

    public function testEventListPageIsAccessible()
    {
        $client = static::createClient();
        $client->request('GET', '/events');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Upcoming Events');
    }

    public function testEventDetailPageIsAccessible()
    {
        $client = static::createClient();
        $event = $this->createEvent();
        $client->request('GET', '/event/' . $event->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $event->getTitle());
    }

    public function testEditEventPageIsAccessible()
    {
        $client = static::createClient();
        $event = $this->createEvent();
        $client->request('GET', '/event/edit/' . $event->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Edit Event');
    }

    public function testDeleteEventPageIsAccessible()
    {
        $client = static::createClient();
        $event = $this->createEvent();
        $client->request('GET', '/event/delete/' . $event->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Delete Event');
    }

    public function testSubscribedEventsPageIsAccessible()
    {
        $client = static::createClient();
        $client->request('GET', '/events/subscribed');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Subscribed Events');
    }

    private function createEvent(): Event
    {
        $entityManager = self::$container->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail('user@example.com')
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setPassword('Password123!');
        $entityManager->persist($user);
        $entityManager->flush();

        $event = new Event();
        $event->setTitle('Test Event')
            ->setDescription('Test event description.')
            ->setDate(new \DateTime('+2 days'))
            ->setMaxParticipants(100)
            ->setIsPublic(true)
            ->setCreatedBy($user);

        $entityManager->persist($event);
        $entityManager->flush();

        return $event;
    }
}
