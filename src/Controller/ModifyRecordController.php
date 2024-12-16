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
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/modify-record', name: 'modify_record')]
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

	public function __invoke(): Response {
		if ($this->getUser() === null) {
			throw $this->createAccessDeniedException();
		}
		$request = $this->requestStack->getCurrentRequest();
		$body = json_decode($request->getContent(), true);
		try {
			$zone = $this->getZone($body);
			if ($body['zoneType'] === 'template') {
				$this->modifyTemplateRecord($zone, $body, $request->getMethod());
			} else {
				$this->modifyPDNSRecord($zone, $body, $request->getMethod());
			}
		} catch (\Exception $e) {
			return new Response($e->getMessage(), 500);
		}

		return new Response();
	}

	private function modifyPDNSRecord($zone, $body, $method): void {
		switch ($method) {
			case 'PUT':
				$pdnsZone = $this->pdnsProvider->get()->zone($zone->getName());
				$this->pdnsProvider->updateRecord($pdnsZone, $body['old_record'], $body['record']);
				break;
			case 'POST':
				$pdnsZone = $this->pdnsProvider->get()->zone($zone->getName());
				$this->pdnsProvider->createRecord($pdnsZone, $body['record']);
				break;
			case 'DELETE':
				$pdnsZone = $this->pdnsProvider->get()->zone($zone->getName());
				$this->pdnsProvider->deleteRecord($pdnsZone, $body['record']);
				break;
		}
	}

	private function modifyTemplateRecord($zone, $body, $method): void {
		switch ($method) {
			case 'PUT':
				$templateRecord = $this->templateRecordRepository->findOneBy(['id' => $body['old_record']['id']]);
				$templateRecord->setRecordData($body['record']);
				$this->entityManager->persist($templateRecord);
				break;
			case 'POST':
				$templateRecord = new TemplateRecord($zone, $body['record']);
				$this->entityManager->persist($templateRecord);
				break;
			case 'DELETE':
				$templateRecord = $this->templateRecordRepository->findOneBy(['id' => $body['record']['id']]);
				$this->entityManager->remove($templateRecord);
		}
		$this->entityManager->flush();
	}

	private function getZone(array $body): object {
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
			throw new BadRequestException('Invalid zone type');
		}

		if ($zone === null) {
			throw new NotFoundHttpException('Zone not found');
		}

		if (!$this->isGranted($permission, $zone)) {
			throw new AccessDeniedException('You do not have permission to modify this zone');
		}

		return $zone;
	}
}
