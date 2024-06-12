<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DataFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        // Create a sample user
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $manager->persist($user);

        // Create sample events
        for ($i = 1; $i <= 10; $i++) {
            $event = new Event();
            $event->setTitle("Event $i");
            $event->setDescription("Description for Event $i");
            $event->setDate(new \DateTime(sprintf('+%d days', $i)));
            $event->setMaxParticipants(100);
            $event->setIsPublic(true);
            $event->setCreatedBy($user);
            $manager->persist($event);
        }

        $manager->flush();
    }
}
