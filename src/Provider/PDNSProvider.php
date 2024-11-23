<?php

namespace App\Provider;

use Exonet\Powerdns\Powerdns;

class PDNSProvider {

	private Powerdns $pdns;

	public function __construct(){
		$this->pdns = new Powerdns($_ENV['PDNS_API_URL'], $_ENV['PDNS_API_KEY']);
	}

	public function get(): Powerdns {
		return $this->pdns;
	}

	public function resourceRecordsToSingleRecords(array $resourceRecords): array {
		$records = [];
		foreach ($resourceRecords as $resourceRecord) {
			foreach ($resourceRecord->getRecords() as $record) {
				$records[] = [
					'name' => $resourceRecord->getName(),
					'type' => $resourceRecord->getType(),
					'ttl' => $resourceRecord->getTtl(),
					'content' => $record->getContent(),
				];
			}
		}
		return $records;
	}

}
