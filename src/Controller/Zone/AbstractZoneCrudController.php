<?php

namespace PowerADM\Controller\Zone;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Exonet\Powerdns\Powerdns;
use PowerADM\Entity\User;
use PowerADM\Provider\PDNSProvider;
use PowerADM\Repository\TemplateRepository;

abstract class AbstractZoneCrudController extends AbstractCrudController {
	protected Powerdns $pdns;

	public function __construct(protected PDNSProvider $pdnsProvider, private EntityManagerInterface $entityManager, private TemplateRepository $templateRepository) {
		$this->pdns = $pdnsProvider->get();
	}

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

	public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void {
		$zone = $this->pdns->createZone($entityInstance->getName(), []);
		$zone->resource()->setKind($entityInstance->getType());
		$entityInstance->setSerial($zone->resource()->getSerial());
		if($entityInstance->getTemplate() !== null) {
			$template = $this->templateRepository->find($entityInstance->getTemplate());
			foreach ($template->getTemplateRecords() as $templateRecord) {
				$arrRecord = [
					'name' => $templateRecord->getName(),
					'type' => $templateRecord->getType(),
					'content' => $templateRecord->getContent(),
					'ttl' => $templateRecord->getTtl()
				];
				$this->pdnsProvider->createRecord($zone, $arrRecord);
			}
		}
		$entityManager->persist($entityInstance);
		$entityManager->flush();
		$this->addFlash('success', 'Zone created successfully');
	}

	public function deleteEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void {
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

	public function configureFields(string $pageName): iterable {
		yield IdField::new('id')->onlyOnIndex();
		yield TextField::new('name')
						->setColumns('col-8 col-xl-6 col-xxl-4')
		;
		yield ChoiceField::new('type')
						->setChoices([
							'Native' => 'Native',
							'Master' => 'Master',
							'Slave' => 'Slave',
						])->renderExpanded()
		;
		yield FormField::addRow();
		yield IntegerField::new('serial')->hideOnForm();

		$templates = $this->templateRepository->findAll();
		$choices = [];
		foreach ($templates as $template) {
			$choices[$template->getName()] = $template->getId();
		}
		yield ChoiceField::new('template')
						->setChoices($choices)
						->onlyWhenCreating()
		;
	}

	public function detail(AdminContext $context) {
		$zone = $context->getEntity()->getInstance();
		$this->pdnsProvider->syncZoneFromPDNS($zone);
		$pdnsZone = $this->pdns->zone($zone->getName());
		$records = $pdnsZone->resource()->getResourceRecords();
		$responseParameters = parent::detail($context);
		$responseParameters->set('zone', $zone->getId());
		$responseParameters->set('records', $this->pdnsProvider->resourceRecordsToSingleRecords($records, $pdnsZone->getCanonicalName()));

		return $responseParameters;
	}
}
