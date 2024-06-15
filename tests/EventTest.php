<?php

namespace App\Tests\Entity;

use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class EventTest extends TestCase
{
    public function testEventCreation()
    {
        $event = new Event();
        $date = new \DateTime('+1 day');
        $user = new User();

        $event->setTitle('Test Event')
            ->setDescription('This is a test event.')
            ->setDate($date)
            ->setMaxParticipants(100)
            ->setIsPublic(true)
            ->setCreatedBy($user)
            ->setIsPaid(true)
            ->setPrice('50.00');

        $this->assertEquals('Test Event', $event->getTitle());
        $this->assertEquals('This is a test event.', $event->getDescription());
        $this->assertEquals($date, $event->getDate());
        $this->assertEquals(100, $event->getMaxParticipants());
        $this->assertTrue($event->getIsPublic());
        $this->assertEquals($user, $event->getCreatedBy());
        $this->assertTrue($event->getIsPaid());
        $this->assertEquals('50.00', $event->getPrice());
    }

    public function testAddRemoveRegistration()
    {
        $event = new Event();
        $registration = $this->createMock(Registration::class);
        $registration->method('getEvent')->willReturn($event);

        $event->addRegistration($registration);
        $this->assertCount(1, $event->getRegistrations());

        $event->removeRegistration($registration);
        $this->assertCount(0, $event->getRegistrations());
    }

    public function testValidation()
    {
        $event = new Event();
        $date = new \DateTime('+1 day');
        $user = new User();

        $event->setTitle('')
            ->setDescription('')
            ->setDate($date)
            ->setMaxParticipants(100)
            ->setIsPublic(true)
            ->setCreatedBy($user);

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        $violations = $validator->validate($event);

        $this->assertGreaterThan(0, count($violations));

        $event->setTitle('Test Event')
            ->setDescription('This is a test event.')
            ->setDate($date)
            ->setMaxParticipants(100)
            ->setIsPublic(true)
            ->setCreatedBy($user)
            ->setIsPaid(true)
            ->setPrice('50.00');

        $violations = $validator->validate($event);
        $this->assertCount(0, $violations);
    }
}
