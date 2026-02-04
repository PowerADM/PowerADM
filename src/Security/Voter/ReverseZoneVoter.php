<?php

namespace App\Security\Voter;

use App\Entity\ReverseZone;
use App\Entity\User;

final class ReverseZoneVoter extends AbstractZoneVoter {
	protected function getAttribute(): string {
		return 'REVERSE_ZONE_EDIT';
	}

	protected function getAllowedZones(User $user): array {
		$zones = [];
		foreach ($user->getAllowedReverseZones() as $zone) {
			$zones[] = $zone->getId();
		}

		return $zones;
	}

	protected function getSupportedClass(): string {
		return ReverseZone::class;
	}
}
