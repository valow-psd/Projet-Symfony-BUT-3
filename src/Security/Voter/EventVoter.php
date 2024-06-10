<?php

namespace App\Security\Voter;

use App\Entity\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class EventVoter extends Voter
{
    const VIEW = 'view';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // Only vote on Event objects inside this voter
        return $attribute === self::VIEW && $subject instanceof Event;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            // User must be logged in; if not, deny access
            return false;
        }

        // The subject is an Event object, thanks to supports()
        /** @var Event $event */
        $event = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($event, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Event $event, UserInterface $user): bool
    {
        if ($event->getIsPublic()) {
            return true;
        }

        return $event->getCreatedBy() === $user;
    }
}
