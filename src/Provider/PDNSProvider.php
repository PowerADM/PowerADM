<?php

namespace PowerADM\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Exonet\Powerdns\Connector;
use Exonet\Powerdns\Powerdns;
use Exonet\Powerdns\Resources\Record;
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

	public function getConnector(): Connector {
		return new Connector($this->pdns);
	}

	public function resourceRecordsToSingleRecords(array $resourceRecords, string $zone): array {
		$records = [];
		foreach ($resourceRecords as $resourceRecord) {
			$i = 0;
			foreach ($resourceRecord->getRecords() as $record) {
				$comment = '';
				if (isset($resourceRecord->getComments()[$i])) {
					$comment = $resourceRecord->getComments()[$i]->getContent();
				}
				$records[] = [
					'displayName' => preg_replace('/\.@$/', '', preg_replace('/'.$zone.'$/', '@', $resourceRecord->getName())),
					'name' => $resourceRecord->getName(),
					'type' => $resourceRecord->getType(),
					'ttl' => $resourceRecord->getTtl(),
					'content' => $record->getContent(),
					'comment' => $comment,
				];
				++$i;
			}
		}

		return $this->sortRecords($records);
	}

	public function sortRecords(array $records, array $orderBy = ['displayName', 'type', 'content']): array {
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

	public function syncZoneFromPDNS($zone): void {
		$pdnsZone = $this->pdns->zone($zone->getName());
		$resource = $pdnsZone->resource();
		$zone->setType($resource->getKind());
		$zone->setSerial($resource->getSerial());
		$this->entityManager->persist($zone);
		$this->entityManager->flush();
	}

	public function syncZonesFromPDNS(bool $forward = true): void {
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
			if ($forward) {
				$zone = new ForwardZone();
			} else {
				$zone = new ReverseZone();
			}
			$zone->setName($name);
			$zone->setType($resource->getKind());
			$zone->setSerial($resource->getSerial());

			$this->entityManager->persist($zone);
		}
		$this->entityManager->flush();
	}

	public function createRecord($zone, $record): void {
		$records = $zone->find($record['name'], $record['type']);

		if (isset($records[0]) === false) {
			$zone->create($record['name'], $record['type'], $record['content'], $record['ttl']);
		} else {
			$result = $records[0];
			$rrs = $result->getRecords();
			$rrs[] = new Record($record['content']);
			$result->setRecords($rrs);
			$result->save();
		}
	}

	public function deleteRecord($zone, $record): void {
		$records = $zone->find($record['name'], $record['type']);
		$result = $records[0];

		if (\count($result->getRecords()) > 1) {
			$rrs = $result->getRecords();
			$rrs = array_filter($rrs, function ($rr) use ($record) {
				return $rr->getContent() !== $record['content'];
			});
			$result->setRecords($rrs);
			$result->save();
		} else {
			$records->delete();
		}
	}

	public function updateRecord($zone, $oldRecord, $newRecord): void {
		if ($oldRecord['name'] != $newRecord['name'] || $oldRecord['type'] != $newRecord['type']) {
			$this->deleteRecord($zone, $oldRecord);
			$this->createRecord($zone, $newRecord);

			return;
		}

		$records = $zone->find($oldRecord['name'], $oldRecord['type']);
		$result = $records[0];
		$rrs = $result->getRecords();
		$rrs = array_map(function ($rr) use ($oldRecord, $newRecord) {
			if ($rr->getContent() === $oldRecord['content']) {
				$rr->setContent($newRecord['content']);
			}

			return $rr;
		}, $rrs);
		$result->setTtl($newRecord['ttl']);
		$result->setRecords($rrs);
		$result->save();
	}
}
