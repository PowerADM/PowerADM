<?php

namespace PowerADM\Security\Voter;

use PowerADM\Entity\ReverseZone;
use PowerADM\Entity\User;

final class ReverseZoneVoter extends AbstractZoneVoter {
	protected function getAttribute(): string {
		return 'REVERSE_ZONE_EDIT';
	}

	protected function getAllowedZones(User $user): array {
		return $user->getAllowedReverseZones();
	}

	protected function getSupportedClass(): string {
		return ReverseZone::class;
	}
}
