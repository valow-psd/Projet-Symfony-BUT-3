<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\Registration;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegisterPageIsAccessible()
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Create an account');
    }

    public function testRegisterUser()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form[firstName]' => 'John',
            'registration_form[lastName]' => 'Doe',
            'registration_form[email]' => 'john.doe@example.com',
            'registration_form[plainPassword]' => 'password123',
        ]);

        $client->submit($form);
        $this->assertResponseRedirects('/'); // Adjust the redirect route as necessary

        $client->followRedirect();
//        $this->assertSelectorTextContains('.flash-success', 'Profile updated successfully.');
    }

    public function testRegisterEvent()
    {
        $client = static::createClient();
        $event = $this->createEvent();
        $this->logIn($client);

        $client->request('POST', '/event/register/' . $event->getId());
        $this->assertResponseRedirects('/event/' . $event->getId());

        $client->followRedirect();
//        $this->assertSelectorTextContains('.flash-success', 'You have successfully registered for the event.');
    }

    public function testUnregisterEvent()
    {
        $client = static::createClient();
        $event = $this->createEvent();
        $this->logIn($client);

        $client->request('POST', '/event/unregister/' . $event->getId());
        $this->assertResponseRedirects('/event/' . $event->getId());

        $client->followRedirect();
//        $this->assertSelectorTextContains('.flash-success', 'You have successfully unregistered from the event.');
    }

    private function logIn($client)
    {
        $entityManager = self::$container->get('doctrine')->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        // Assuming you have a user in your fixtures with email 'test@example.com'
        $testUser = $userRepository->findOneByEmail('valentinmunch.edu@gmail.com');

        $client->loginUser($testUser);
    }

    private function createEvent(): Event
    {
        $entityManager = self::$container->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail('organizer@example.com')
            ->setFirstName('Organizer')
            ->setLastName('User')
            ->setPassword('password123');
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
