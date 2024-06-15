<?php

namespace App\Tests\Entity;

use App\Entity\Event;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use DateTime;

class EventTest extends TestCase
{
    public function testValidEvent()
    {
        $user = new User();
        $event = new Event();
        $event->setTitle('Conference on Sustainable Development')
            ->setDescription('A conference dedicated to the latest advances in sustainable development.')
            ->setDate(new DateTime('+2 days'))
            ->setMaxParticipants(150)
            ->setIsPublic(true)
            ->setCreatedBy($user);

        $this->assertEquals('Conference on Sustainable Development', $event->getTitle());
        $this->assertEquals('A conference dedicated to the latest advances in sustainable development.', $event->getDescription());
        $this->assertEquals(150, $event->getMaxParticipants());
        $this->assertTrue($event->getIsPublic());
        $this->assertEquals($user, $event->getCreatedBy());
    }

    public function testEventRegistrations()
    {
        $event = new Event();
        $this->assertCount(0, $event->getRegistrations());
    }
}
