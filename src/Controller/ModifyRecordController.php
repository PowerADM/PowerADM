<?php

namespace PowerADM\Controller;

use PowerADM\Provider\PDNSProvider;
use PowerADM\Repository\ForwardZoneRepository;
use PowerADM\Repository\ReverseZoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModifyRecordController extends AbstractController {
	public function __construct(private PDNSProvider $pdnsProvider, private RequestStack $requestStack, private ForwardZoneRepository $forwardZoneRepository, private ReverseZoneRepository $reverseZoneRepository) {
	}

	#[Route('/api/modify-record', name: 'modify_record', methods: ['PUT'])]
	public function modifyRecord(): Response {
		if ($this->getUser() === null) {
			throw $this->createAccessDeniedException();
		}

		try {
			$request = $this->requestStack->getCurrentRequest();
			$body = json_decode($request->getContent(), true);
			$zone = $this->getZone($body);
			$pdnsZone = $this->pdnsProvider->get()->zone($zone->getName());

			$this->pdnsProvider->updateRecord($pdnsZone, $body['old_record'], $body['record']);
		} catch (\Exception $e) {
			return new Response($e->getMessage(), 500);
		}

		return new Response();
	}

	#[Route('/api/modify-record', name: 'create_record', methods: ['POST'])]
	public function createRecord(): Response {
		if ($this->getUser() === null) {
			throw $this->createAccessDeniedException();
		}

		try {
			$request = $this->requestStack->getCurrentRequest();
			$body = json_decode($request->getContent(), true);
			$zone = $this->getZone($body);
			$pdnsZone = $this->pdnsProvider->get()->zone($zone->getName());

			$this->pdnsProvider->createRecord($pdnsZone, $body['record']);
		} catch (\Exception $e) {
			return new Response($e->getMessage(), 500);
		}

		return new Response();
	}

	#[Route('/api/modify-record', name: 'delete_record', methods: ['DELETE'])]
	public function deleteRecord(): Response {
		if ($this->getUser() === null) {
			throw $this->createAccessDeniedException();
		}

		try {
			$request = $this->requestStack->getCurrentRequest();
			$body = json_decode($request->getContent(), true);
			$zone = $this->getZone($body);
			$pdnsZone = $this->pdnsProvider->get()->zone($zone->getName());

			$this->pdnsProvider->deleteRecord($pdnsZone, $body['record']);
		} catch (\Exception $e) {
			return new Response($e->getMessage(), 500);
		}

		return new Response();
	}

	public function getZone($body): object {
		$zone = null;
		if ($body['zoneType'] === 'forward') {
			$permission = 'FORWARD_ZONE_EDIT';
			$zone = $this->forwardZoneRepository->find($body['zone']);
		} elseif ($body['zoneType'] === 'reverse') {
			$permission = 'REVERSE_ZONE_EDIT';
			$zone = $this->reverseZoneRepository->find($body['zone']);
		}

		if ($zone === null) {
			throw new \Exception('Zone not found');
		}

		if (!$this->isGranted($permission, $zone)) {
			throw $this->createAccessDeniedException();
		}

		return $zone;
	}
}
