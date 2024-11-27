<?php

namespace PowerADM\Controller\Zone;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Exonet\Powerdns\Powerdns;
use PowerADM\Entity\User;
use PowerADM\Provider\PDNSProvider;

abstract class AbstractZoneCrudController extends AbstractCrudController {
	protected Powerdns $pdns;

	public function __construct(protected PDNSProvider $pdnsProvider, private EntityManagerInterface $entityManager) {
		$this->pdns = $pdnsProvider->get();
	}

	abstract public static function getEntityFqcn(): string;

	public function configureActions(Actions $actions): Actions {
		$actions = $actions
					->disable(Action::EDIT)
					->disable(Action::BATCH_DELETE)
					->add(Crud::PAGE_INDEX, Action::DETAIL)
		;
		if (!$this->isGranted('ROLE_ADMIN')) {
			$actions = $actions
						->disable(Action::NEW)
						->disable(Action::DELETE)
			;
		}

		return $actions;
	}

	public function configureCrud(Crud $crud): Crud {
		return $crud
				->setSearchFields(['name'])
				->renderContentMaximized()
				->showEntityActionsInlined(true)
				->overrideTemplate('crud/detail', 'zone_detail.html.twig')
		;
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

	protected function getUser(): User {
		return parent::getUser();
	}
}
