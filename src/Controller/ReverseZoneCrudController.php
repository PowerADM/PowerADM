<?php

namespace App\Controller;

use App\Entity\ReverseZone;
use App\Provider\PDNSProvider;
use App\Repository\ReverseZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Exonet\Powerdns\Powerdns;

#[AdminCrud(routePath: '/zones/reverse', routeName: 'reversezone')]
class ReverseZoneCrudController extends AbstractCrudController {
	private Powerdns $pdns;

	public function __construct(private PDNSProvider $pdnsProvider, private ReverseZoneRepository $reverseZoneRepository, private EntityManagerInterface $entityManager) {
		$this->pdns = $pdnsProvider->get();

		$zones = $this->pdns->listZones();
		foreach ($zones as $zone) {
			$name = $zone->getCanonicalName();
			if (!str_ends_with($name, '.in-addr.arpa.') && !str_ends_with($name, '.ip6.arpa.')) {
				continue;
			}
			$resource = $zone->resource();
			if ($localZone = $this->reverseZoneRepository->findOneBy(['name' => $name])) {
				$localZone->setType($resource->getKind());
				$localZone->setSerial($resource->getSerial());
				$this->entityManager->persist($localZone);
				$this->entityManager->flush();
				continue;
			}
			$reverseZone = new ReverseZone();
			$reverseZone->setName($name);
			$reverseZone->setType($resource->getKind());
			$reverseZone->setSerial($resource->getSerial());

			$this->entityManager->persist($reverseZone);
			$this->entityManager->flush();
		}
	}

	public static function getEntityFqcn(): string {
		return ReverseZone::class;
	}

	public function configureCrud(Crud $crud): Crud {
		return $crud
				->setEntityLabelInSingular('Reverse Zone')
				->setEntityLabelInPlural('Reverse Zones')
				->setSearchFields(['name'])
		;
	}

	public function detail(AdminContext $context){
		$zone = $this->pdns->zone($context->getEntity()->getInstance()->getName());
		$records = $zone->resource()->getResourceRecords();
		$responseParameters = parent::detail($context);
		$responseParameters->set('templatePath', 'zone_detail.html.twig');
		$responseParameters->set('templateName', '');
		$responseParameters->set('zone', $zone);
		$responseParameters->set('records', $this->pdnsProvider->resourceRecordsToSingleRecords($records));
		return $responseParameters;
	}

	public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void {
		$zone = $this->pdns->createZone($entityInstance->getName(), []);
		$zone->resource()->setKind($entityInstance->getType());
		$entityInstance->setSerial($zone->resource()->getSerial());

		$entityManager->persist($entityInstance);
		$entityManager->flush();
		$this->addFlash('success', 'Zone created successfully');
	}

	public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void {
		try {
			$this->pdns->deleteZone($entityInstance->getName());
		} catch (\Exception $e) {
			$this->addFlash('error', 'Failed to delete zone on PDNS instance: '.$e->getMessage());
		}

		$entityManager->remove($entityInstance);
		$entityManager->flush();
		$this->addFlash('success', 'Zone deleted successfully');
	}
}
