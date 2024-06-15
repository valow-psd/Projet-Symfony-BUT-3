<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageIsAccessible()
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Log in to your account');
    }

    public function testLoginWithInvalidCredentials()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Sign in')->form([
            '_username' => 'wrong_user',
            '_password' => 'wrong_password',
        ]);

        $client->submit($form);
        $this->assertResponseRedirects('/login');

        $client->followRedirect();
//        $this->assertSelectorExists('.alert-danger');
    }

    public function testLoginWithValidCredentials()
    {
        $client = static::createClient();
        $entityManager = self::$container->get('doctrine')->getManager();

        // Assuming you have a user in your fixtures with email 'test@example.com' and password 'password123'
        $userRepository = $entityManager->getRepository(User::class);
        $testUser = $userRepository->findOneByEmail('contact@valow.xyz');

        // Manually set the password for testing purposes
        $testUser->setPassword(
            self::$container->get('security.password_encoder')->encodePassword($testUser, 'password123')
        );
        $entityManager->flush();

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Sign in')->form([
            '_username' => 'contact@valow.xyz',
            '_password' => 'Azerty14@',
        ]);

        $client->submit($form);
        $this->assertResponseRedirects('/'); // Adjust the redirect route as necessary

        $client->followRedirect();
//        $this->assertSelectorTextContains('h1', 'Welcome'); // Adjust according to your actual homepage content
    }

    public function testLogout()
    {
        $client = static::createClient();
        $client->request('GET', '/logout');

        $this->assertResponseRedirects('/login');
    }
}
