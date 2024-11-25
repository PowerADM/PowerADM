<?php

namespace PowerADM\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Exonet\Powerdns\Powerdns;
use PowerADM\Entity\ForwardZone;
use PowerADM\Entity\ReverseZone;
use PowerADM\Repository\ForwardZoneRepository;
use PowerADM\Repository\ReverseZoneRepository;

class PDNSProvider {
	private Powerdns $pdns;

	public function __construct(private ForwardZoneRepository $forwardZoneRepository, private ReverseZoneRepository $reverseZoneRepository, private EntityManagerInterface $entityManager) {
		$this->pdns = new Powerdns($_ENV['PDNS_API_URL'], $_ENV['PDNS_API_KEY']);
	}

	public function get(): Powerdns {
		return $this->pdns;
	}

	public function resourceRecordsToSingleRecords(array $resourceRecords, string $zone): array {
		$records = [];
		foreach ($resourceRecords as $resourceRecord) {
			foreach ($resourceRecord->getRecords() as $record) {
				$records[] = [
					'displayName' => preg_replace('/\.@$/', '', preg_replace('/' . $zone . '$/', '@', $resourceRecord->getName())),
					'name' => $resourceRecord->getName(),
					'type' => $resourceRecord->getType(),
					'ttl' => $resourceRecord->getTtl(),
					'content' => $record->getContent(),
				];
			}
		}

		return $this->sortRecords($records);
	}

	public function sortRecords(array $records, array $orderBy = ['displayName', 'type']): array {
		usort($records, function ($a, $b) use ($orderBy) {
			foreach ($orderBy as $key) {
				if ($a[$key] === $b[$key]) {
					continue;
				}

				return $a[$key] <=> $b[$key];
			}

			return 0;
		});

		return $records;
	}

	public function updateZones(bool $forward = true) {
		$zones = $this->pdns->listZones();
		foreach ($zones as $zone) {
			$name = $zone->getCanonicalName();
			$resource = $zone->resource();
			$localZone = null;
			if ($forward) {
				if (str_ends_with($name, '.in-addr.arpa.') || str_ends_with($name, '.ip6.arpa.')) {
					continue;
				}
				$localZone = $this->forwardZoneRepository->findOneBy(['name' => $name]);
			} else {
				if (!str_ends_with($name, '.in-addr.arpa.') && !str_ends_with($name, '.ip6.arpa.')) {
					continue;
				}
				$localZone = $this->reverseZoneRepository->findOneBy(['name' => $name]);
			}
			if ($localZone) {
				$localZone->setType($resource->getKind());
				$localZone->setSerial($resource->getSerial());
				$this->entityManager->persist($localZone);
				continue;
			}
			if($forward) {
				$zone = new ForwardZone();
			}else{
				$zone = new ReverseZone();
			}
			$zone->setName($name);
			$zone->setType($resource->getKind());
			$zone->setSerial($resource->getSerial());

			$this->entityManager->persist($zone);
		}
		$this->entityManager->flush();
	}
}
