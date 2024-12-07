<?php

namespace PowerADM\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PowerADM\Entity\TemplateRecord;
use PowerADM\Provider\PDNSProvider;
use PowerADM\Repository\ForwardZoneRepository;
use PowerADM\Repository\ReverseZoneRepository;
use PowerADM\Repository\TemplateRecordRepository;
use PowerADM\Repository\TemplateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModifyRecordController extends AbstractController {
	public function __construct(
		private PDNSProvider $pdnsProvider,
		private RequestStack $requestStack,
		private ForwardZoneRepository $forwardZoneRepository,
		private ReverseZoneRepository $reverseZoneRepository,
		private TemplateRepository $templateRepository,
		private TemplateRecordRepository $templateRecordRepository,
		private EntityManagerInterface $entityManager
	) {
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
			if ($body['zoneType'] === 'template') {
				$templateRecord = $this->templateRecordRepository->findOneBy(['id' => $body['old_record']['id']]);
				$templateRecord->setName($body['record']['name']);
				$templateRecord->setTtl($body['record']['ttl']);
				$templateRecord->setType($body['record']['type']);
				$templateRecord->setContent($body['record']['content']);
				$templateRecord->setComment($body['record']['comment']);
				$this->entityManager->persist($templateRecord);
				$this->entityManager->flush();

				return new Response();
			}

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
			if ($body['zoneType'] === 'template') {
				$templateRecord = new TemplateRecord();
				$templateRecord->setName($body['record']['name']);
				$templateRecord->setTtl($body['record']['ttl']);
				$templateRecord->setType($body['record']['type']);
				$templateRecord->setContent($body['record']['content']);
				$templateRecord->setComment($body['record']['comment']);
				$templateRecord->setTemplate($zone);
				$zone->addTemplateRecord($templateRecord);
				$this->entityManager->persist($templateRecord);
				$this->entityManager->flush();

				return new Response();
			}
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
			if ($body['zoneType'] === 'template') {
				$templateRecord = $this->templateRecordRepository->findOneBy(['id' => $body['record']['id']]);
				$this->entityManager->remove($templateRecord);
				$this->entityManager->flush();
				return new Response();
			}
			$pdnsZone = $this->pdnsProvider->get()->zone($zone->getName());

			$this->pdnsProvider->deleteRecord($pdnsZone, $body['record']);
		} catch (\Exception $e) {
			return new Response($e->getMessage(), 500);
		}

		return new Response();
	}

	public function getZone(array $body): object {
		if ($body['zoneType'] === 'forward') {
			$permission = 'FORWARD_ZONE_EDIT';
			$zone = $this->forwardZoneRepository->find($body['zone']);
		} elseif ($body['zoneType'] === 'reverse') {
			$permission = 'REVERSE_ZONE_EDIT';
			$zone = $this->reverseZoneRepository->find($body['zone']);
		} elseif ($body['zoneType'] === 'template') {
			$permission = 'ROLE_ADMIN';
			$zone = $this->templateRepository->find($body['zone']);
		} else {
			throw new \Exception('Invalid zone type');
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
