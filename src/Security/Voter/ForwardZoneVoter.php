<?php

namespace PowerADM\Security\Voter;

use PowerADM\Entity\ForwardZone;
use PowerADM\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ForwardZoneVoter extends Voter {
	public const EDIT = 'FORWARD_ZONE_EDIT';

	public function __construct(private Security $security) {
	}

	protected function supports(string $attribute, mixed $subject): bool {
		return \in_array($attribute, [self::EDIT])
			&& $subject instanceof ForwardZone;
	}

	protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool {
		$user = $token->getUser();
		if (!$user instanceof User) {
			return false;
		}

		switch ($attribute) {
			case self::EDIT:
				if ($this->security->isGranted('ROLE_EDITOR')) {
					return true;
				}
				if (\in_array($subject->getId(), $user->getAllowedForwardZones())) {
					return true;
				}
				break;
		}

		return false;
	}
}
