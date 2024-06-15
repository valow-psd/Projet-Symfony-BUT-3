<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    public function testProfilePageIsAccessible()
    {
        $client = static::createClient();
        $this->logIn($client);
        $client->request('GET', '/profile');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Update your profile');
    }

    public function testUpdateProfile()
    {
        $client = static::createClient();
        $this->logIn($client);
        $crawler = $client->request('GET', '/profile');

        $form = $crawler->selectButton('Save')->form([
            'profile_form[firstName]' => 'John',
            'profile_form[lastName]' => 'Doe',
            'profile_form[email]' => 'john.doe@example.com',
            'profile_form[plainPassword]' => 'Newpassword123!',
        ]);

        $client->submit($form);
        $this->assertResponseRedirects('/profile');

        $client->followRedirect();
//        $this->assertSelectorTextContains('.flash-success', 'Profile updated successfully.');
    }

    private function logIn($client)
    {
        $entityManager = self::$container->get('doctrine')->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        // Assuming you have a user in your fixtures with email 'test@example.com'
        $testUser = $userRepository->findOneByEmail('contact@valow.xyz');

        $client->loginUser($testUser);
    }
}
