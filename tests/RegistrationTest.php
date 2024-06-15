<?php

namespace App\Tests\Entity;

use App\Entity\Registration;
use App\Entity\User;
use App\Entity\Event;
use PHPUnit\Framework\TestCase;

class RegistrationTest extends TestCase
{
    public function testRegistrationCreation()
    {
        $registration = new Registration();
        $user = new User();
        $event = new Event();
        $date = new \DateTime();
        $uniqueCode = bin2hex(random_bytes(8));
        $amount = 50.0;

        $registration->setUser($user)
            ->setEvent($event)
            ->setCreatedAt($date)
            ->setAmount($amount)
            ->setUniqueCode($uniqueCode);

        $this->assertEquals($user, $registration->getUser());
        $this->assertEquals($event, $registration->getEvent());
        $this->assertEquals($date, $registration->getCreatedAt());
        $this->assertEquals($amount, $registration->getAmount());
        $this->assertEquals($uniqueCode, $registration->getUniqueCode());
    }

    public function testUniqueCode()
    {
        $registration = new Registration();
        $uniqueCode = bin2hex(random_bytes(8));
        $registration->setUniqueCode($uniqueCode);

        $this->assertEquals($uniqueCode, $registration->getUniqueCode());
    }

    public function testAmount()
    {
        $registration = new Registration();
        $amount = 100.0;
        $registration->setAmount($amount);

        $this->assertEquals($amount, $registration->getAmount());
    }

    public function testUser()
    {
        $registration = new Registration();
        $user = new User();
        $registration->setUser($user);

        $this->assertEquals($user, $registration->getUser());
    }

    public function testEvent()
    {
        $registration = new Registration();
        $event = new Event();
        $registration->setEvent($event);

        $this->assertEquals($event, $registration->getEvent());
    }

    public function testCreatedAt()
    {
        $registration = new Registration();
        $date = new \DateTime();
        $registration->setCreatedAt($date);

        $this->assertEquals($date, $registration->getCreatedAt());
    }
}
