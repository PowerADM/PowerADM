<?php

namespace PowerADM\Controller;

use PowerADM\Provider\PDNSProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModifyRecordController extends AbstractController {

	public function __construct(private PDNSProvider $pdnsProvider, private RequestStack $requestStack) {
	}

	#[Route('/api/modify-record', name: 'modify_record', methods: ['PUT'])]
	public function modifyRecord(): Response {
		if ($this->getUser() === null) {
			throw $this->createAccessDeniedException();
		}

		return new Response();
	}
	#[Route('/api/modify-record', name: 'create_record', methods: ['POST'])]
	public function createRecord(): Response {
		if ($this->getUser() === null) {
			throw $this->createAccessDeniedException();
		}

		return new Response();
	}
	#[Route('/api/modify-record', name: 'delete_record', methods: ['DELETE'])]
	public function deleteRecord(): Response {
		if ($this->getUser() === null) {
			throw $this->createAccessDeniedException();
		}

		$request = $this->requestStack->getCurrentRequest();
		$body = json_decode($request->getContent(), true);
		[$zone, $name, $type] = [$body['zone'], $body['record']['name'], $body['record']['type']];

		$records = $this->pdnsProvider->get()->zone($zone)->find($name, $type);
		$result = $records[0];

		if(sizeof($result->getRecords()) > 1){
			$rrs = $result->getRecords();
			$rrs = array_filter($rrs, function($rr) use ($body){
				return $rr->getContent() !== $body['record']['content'];
			});
			$result->setRecords($rrs);
			$result->save();
		} else {
			$records->delete();
		}

		return new Response();
	}
}
