<?php

namespace PowerADM\Security\Voter;

use PowerADM\Entity\ForwardZone;
use PowerADM\Entity\User;

final class ForwardZoneVoter extends AbstractZoneVoter {
	protected function getAllowedZones(User $user): array {
		return $user->getAllowedForwardZones();
	}

	protected function getAttribute(): string {
		return 'FORWARD_ZONE_EDIT';
	}

	protected function getSupportedClass(): string {
		return ForwardZone::class;
	}
}
