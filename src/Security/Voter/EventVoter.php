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
    const EDIT = 'edit';
    const DELETE = 'delete';


    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // Only vote on Event objects inside this voter
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE]) && $subject instanceof Event;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
    
        if (!$user instanceof UserInterface) {
            return false;
        }
    
        /** @var Event $event */
        $event = $subject;
    
        switch ($attribute) {
            case self::VIEW:
                return $this->canView($event, $user);
            case self::EDIT:
            case self::DELETE:
                return $this->canEditOrDelete($event, $user);
            default:
                throw new \LogicException('This code should not be reached!');
        }
    }
    

    private function canView(Event $event, UserInterface $user): bool
    {
        if ($event->getIsPublic()) {
            return true;
        }

        return $event->getCreatedBy() === $user;
    }

    private function canEditOrDelete(Event $event, UserInterface $user): bool
    {
        return $event->getCreatedBy() === $user;
    }

}
