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
use PowerADM\Entity\ReverseZone;
use PowerADM\Provider\PDNSProvider;
use PowerADM\Repository\ReverseZoneRepository;

#[AdminCrud(routePath: '/zones/reverse', routeName: 'reversezone')]
class ReverseZoneCrudController extends AbstractCrudController {
	private Powerdns $pdns;

	public function __construct(private PDNSProvider $pdnsProvider, private ReverseZoneRepository $reverseZoneRepository, private EntityManagerInterface $entityManager) {
		$this->pdns = $pdnsProvider->get();
		$pdnsProvider->updateZones(false);
	}

	public static function getEntityFqcn(): string {
		return ReverseZone::class;
	}

	public function configureCrud(Crud $crud): Crud {
		return $crud
				->setEntityLabelInSingular('Reverse Zone')
				->setEntityLabelInPlural('Reverse Zones')
				->setSearchFields(['name'])
				->renderContentMaximized()
				->showEntityActionsInlined(true)
				->overrideTemplate('crud/detail', 'zone_detail.html.twig')
				->setPageTitle('detail', fn (ReverseZone $reverseZone) => sprintf('Reverse Zone - %s', $reverseZone->getName()))
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
		$responseParameters->set('records', $this->pdnsProvider->resourceRecordsToSingleRecords($records, $zone->getCanonicalName()));

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
