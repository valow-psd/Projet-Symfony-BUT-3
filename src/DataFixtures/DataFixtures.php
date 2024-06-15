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
        $events = [
            [
                'title' => 'Sustainable Development Conference',
                'description' => 'A conference dedicated to the latest advances in sustainable development.',
                'date' => new \DateTime('+2 days'),
                'maxParticipants' => 150,
                'isPublic' => true,
                'isPaid' => true,
                'price' => 50.00
            ],
            [
                'title' => 'Vegetarian Cooking Workshop',
                'description' => 'A hands-on workshop to learn how to cook delicious vegetarian dishes.',
                'date' => new \DateTime('+5 days'),
                'maxParticipants' => 20,
                'isPublic' => false,
                'isPaid' => true,
                'price' => 30.00
            ],
            [
                'title' => 'City Marathon',
                'description' => 'Participate in the annual city marathon and test your endurance.',
                'date' => new \DateTime('+10 days'),
                'maxParticipants' => 1000,
                'isPublic' => true,
                'isPaid' => false,
                'price' => 0.00
            ],
            [
                'title' => 'Contemporary Art Exhibition',
                'description' => 'Discover contemporary art pieces from various artists.',
                'date' => new \DateTime('+15 days'),
                'maxParticipants' => 200,
                'isPublic' => true,
                'isPaid' => false,
                'price' => 0.00
            ],
            [
                'title' => 'Digital Marketing Seminar',
                'description' => 'Learn the latest digital marketing techniques at this seminar.',
                'date' => new \DateTime('+20 days'),
                'maxParticipants' => 50,
                'isPublic' => false,
                'isPaid' => true,
                'price' => 40.00
            ],
            [
                'title' => 'Classical Music Concert',
                'description' => 'Attend a classical music concert performed by a renowned orchestra.',
                'date' => new \DateTime('+25 days'),
                'maxParticipants' => 300,
                'isPublic' => true,
                'isPaid' => true,
                'price' => 25.00
            ],
            [
                'title' => 'Outdoor Yoga Class',
                'description' => 'Join us for an outdoor yoga class suitable for all levels.',
                'date' => new \DateTime('+30 days'),
                'maxParticipants' => 100,
                'isPublic' => true,
                'isPaid' => false,
                'price' => 0.00
            ],
            [
                'title' => 'Outdoor Movie Screening',
                'description' => 'Enjoy an evening of outdoor cinema with a popular movie.',
                'date' => new \DateTime('+35 days'),
                'maxParticipants' => 500,
                'isPublic' => true,
                'isPaid' => false,
                'price' => 0.00
            ],
            [
                'title' => 'Artificial Intelligence Conference',
                'description' => 'Explore the latest innovations in AI with industry experts.',
                'date' => new \DateTime('+40 days'),
                'maxParticipants' => 150,
                'isPublic' => true,
                'isPaid' => true,
                'price' => 60.00
            ],
            [
                'title' => 'Creative Writing Workshop',
                'description' => 'A workshop to develop your creative writing skills.',
                'date' => new \DateTime('+45 days'),
                'maxParticipants' => 25,
                'isPublic' => false,
                'isPaid' => true,
                'price' => 20.00
            ],
            [
                'title' => 'Music Festival',
                'description' => 'Enjoy a variety of musical performances at our annual festival.',
                'date' => new \DateTime('+50 days'),
                'maxParticipants' => 2000,
                'isPublic' => true,
                'isPaid' => true,
                'price' => 70.00
            ],
            [
                'title' => 'Grünt Festival',
                'description' => 'Enjoy a variety of emergent francophone rappers in Bobigny Park, Paris.',
                'date' => new \DateTime('+50 days'),
                'maxParticipants' => 800,
                'isPublic' => true,
                'isPaid' => true,
                'price' => 55.00
            ],
            [
                'title' => 'Yardland',
                'description' => 'Rema, Gunna, Shay, Ateyaba, and more! Find them from July 6 to 7 at the Paris-Vincennes racecourse.',
                'date' => new \DateTime('+30 days'),
                'maxParticipants' => 20000,
                'isPublic' => true,
                'isPaid' => true,
                'price' => 79.00
            ],
            [
                'title' => 'Francopholies',
                'description' => 'Le 14 juillet 2024, Les Francofolies fêteront la clôture de leur 40ème édition. Elles vous concocteront un feu d’artifice de surprises plus étonnants les unes que les autres. Car 40 éditions ça se fête ! Au programme : Hervé, figure majeure de la nouvelle scène pop française, Zaho de Sagazan, la triomphante des Victoires de La Musique 2024, Sofiane Pamart, le pianiste virtuose pas si classique, Phoenix, le meilleur groupe français dans le monde, & Jean-Michel Jarre, le père fondateur de la musique électronique ! Venez souffler la quarantaine bougie avec nous, promis...on vous réserve de belles surprises !',
                'date' => new \DateTime('+35 days'),
                'maxParticipants' => 12000,
                'isPublic' => true,
                'isPaid' => true,
                'price' => 65.00
            ],
            [
                'title' => 'Lazer Dim 700 vs Lilkris3000',
                'description' => 'The battle of the century.',
                'date' => new \DateTime('+12 days'),
                'maxParticipants' => 5,
                'isPublic' => true,
                'isPaid' => false,
                'price' => 0.00
            ],
            [
                'title' => 'Le Mans 24 Hours',
                'description' => 'The most historical race in the world made its comeback for the 145th Edition !',
                'date' => new \DateTime('+50 days'),
                'maxParticipants' => 45000,
                'isPublic' => true,
                'isPaid' => true,
                'price' => 75.00
            ]
        ];

        foreach ($events as $eventData) {
            $event = new Event();
            $event->setTitle($eventData['title']);
            $event->setDescription($eventData['description']);
            $event->setDate($eventData['date']);
            $event->setMaxParticipants($eventData['maxParticipants']);
            $event->setIsPublic($eventData['isPublic']);
            $event->setIsPaid($eventData['isPaid']);
            $event->setPrice($eventData['price']);
            $event->setCreatedBy($user);
            $manager->persist($event);
        }

        $manager->flush();
    }
}
