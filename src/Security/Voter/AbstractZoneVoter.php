<?php

namespace PowerADM\Security\Voter;

use PowerADM\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractZoneVoter extends Voter {
	public function __construct(private Security $security) {
	}

	protected function supports(string $attribute, mixed $subject): bool {
		return $attribute == static::getAttribute()
			&& is_a($subject, $this->getSupportedClass())
			|| $subject === null;
	}

	protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool {
		$user = $token->getUser();
		if (!$user instanceof User) {
			return false;
		}
		if ($attribute != static::getAttribute()) {
			return false;
		}

		if ($this->security->isGranted('ROLE_EDITOR')) {
			return true;
		}
		if ($subject === null) {
			return true;
		}

		return (bool) \in_array($subject->getId(), $this->getAllowedZones($user));
	}

	abstract protected function getAllowedZones(User $user): array;

	abstract protected function getAttribute(): string;

	abstract protected function getSupportedClass(): string;
}
