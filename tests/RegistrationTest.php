<?php

namespace App\Tests\Entity;

use App\Entity\Registration;
use App\Entity\User;
use App\Entity\Event;
use PHPUnit\Framework\TestCase;
use DateTime;

class RegistrationTest extends TestCase
{
    public function testValidRegistration()
    {
        $user = new User();
        $event = new Event();
        $registration = new Registration();
        $registration->setUser($user)
            ->setEvent($event)
            ->setCreatedAt(new DateTime('now'));

        $this->assertEquals($user, $registration->getUser());
        $this->assertEquals($event, $registration->getEvent());
        $this->assertInstanceOf(DateTime::class, $registration->getCreatedAt());
    }
}
