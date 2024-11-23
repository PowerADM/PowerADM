<?php

namespace PowerADM\Controller;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Exonet\Powerdns\Powerdns;
use PowerADM\Entity\ForwardZone;
use PowerADM\Provider\PDNSProvider;
use PowerADM\Repository\ForwardZoneRepository;

#[AdminCrud(routePath: '/zones/forward', routeName: 'forwardzones')]
class ForwardZoneCrudController extends AbstractCrudController {
	private Powerdns $pdns;

	public function __construct(private PDNSProvider $pdnsProvider, private ForwardZoneRepository $forwardZoneRepository, private EntityManagerInterface $entityManager) {
		$this->pdns = $pdnsProvider->get();

		$zones = $this->pdns->listZones();
		foreach ($zones as $zone) {
			$name = $zone->getCanonicalName();
			if (str_ends_with($name, '.in-addr.arpa.') || str_ends_with($name, '.ip6.arpa.')) {
				continue;
			}
			$resource = $zone->resource();
			if ($localZone = $this->forwardZoneRepository->findOneBy(['name' => $name])) {
				$localZone->setType($resource->getKind());
				$localZone->setSerial($resource->getSerial());
				$this->entityManager->persist($localZone);
				$this->entityManager->flush();
				continue;
			}
			$forwardZone = new ForwardZone();
			$forwardZone->setName($name);
			$forwardZone->setType($resource->getKind());
			$forwardZone->setSerial($resource->getSerial());

			$this->entityManager->persist($forwardZone);
			$this->entityManager->flush();
		}
	}

	public static function getEntityFqcn(): string {
		return ForwardZone::class;
	}

	public function configureCrud(Crud $crud): Crud {
		return $crud
				->setEntityLabelInSingular('Forward Zone')
				->setEntityLabelInPlural('Forward Zones')
				->setSearchFields(['name'])
				->renderContentMaximized()
				->showEntityActionsInlined(true)
				->overrideTemplate('crud/detail', 'zone_detail.html.twig')
		;
	}

	public function configureActions(Actions $actions): Actions {
		return $actions
			->disable(Action::EDIT)
			->disable(Action::BATCH_DELETE)
			->add(Crud::PAGE_INDEX, Action::DETAIL)
		;
	}

	public function detail(AdminContext $context) {
		$zone = $this->pdns->zone($context->getEntity()->getInstance()->getName());
		$records = $zone->resource()->getResourceRecords();
		$responseParameters = parent::detail($context);
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
