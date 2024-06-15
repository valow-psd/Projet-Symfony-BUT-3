<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Event;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testValidUser()
    {
        $user = new User();
        $user->setEmail('test@example.com')
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setPassword('password123');

        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertEquals('password123', $user->getPassword());
    }

    public function testUserEvents()
    {
        $user = new User();
        $event = new Event();
        $event->setCreatedBy($user);

        $user->addEvent($event);

        $this->assertCount(1, $user->getEvents());
        $this->assertTrue($user->getEvents()->contains($event));

        $user->removeEvent($event);
        $this->assertCount(0, $user->getEvents());
    }
}