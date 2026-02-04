<?php

namespace App\Security\Voter;

use App\Entity\ForwardZone;
use App\Entity\User;

final class ForwardZoneVoter extends AbstractZoneVoter {
	protected function getAllowedZones(User $user): array {
		$zones = [];
		foreach ($user->getAllowedForwardZones() as $zone) {
			$zones[] = $zone->getId();
		}

		return $zones;
	}

	protected function getAttribute(): string {
		return 'FORWARD_ZONE_EDIT';
	}

	protected function getSupportedClass(): string {
		return ForwardZone::class;
	}
}
