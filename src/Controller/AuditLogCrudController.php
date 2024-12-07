<?php

namespace PowerADM\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use PowerADM\Entity\AuditLog;

#[AdminCrud(routePath: '/audit-log', routeName: 'audit_log')]
class AuditLogCrudController extends AbstractCrudController {
	public static function getEntityFqcn(): string {
		return AuditLog::class;
	}

	public function configureCrud(Crud $crud): Crud {
		return $crud
				->setEntityLabelInSingular('Audit Log')
				->setEntityLabelInPlural('Audit Log')
				->renderContentMaximized()
				->showEntityActionsInlined(true)
				->setDefaultSort(['created' => 'DESC'])
		;
	}

	public function configureActions(Actions $actions): Actions {
		return $actions
			->disable(Action::BATCH_DELETE)
			->disable(Action::DELETE)
			->disable(Action::EDIT)
			->disable(Action::NEW)
			->add(Crud::PAGE_INDEX, Action::DETAIL)
		;
	}

	public function configureFields(string $pageName): iterable {
		yield DateTimeField::new('created')
						->setColumns('col-8 col-xl-6 col-xxl-4')
						->setDisabled(true)
		;
		yield AssociationField::new('user')
						->setColumns('col-8 col-xl-6 col-xxl-4')
						->setDisabled(true)
						->formatValue(fn ($value, $entity) => $entity->getUser()->getFullName() ?: $entity->getUser()->getUsername())
		;
		yield ChoiceField::new('action')
						->setColumns('col-8 col-xl-6 col-xxl-4')
						->setDisabled(true)
						->setChoices([
							'Create' => 'CREATE',
							'Update' => 'UPDATE',
							'Delete' => 'DELETE',
						])->renderAsBadges([
							'CREATE' => 'primary',
							'UPDATE' => 'warning',
							'DELETE' => 'danger',
						])->renderExpanded()
		;
		yield ArrayField::new('entity')
						->setColumns('col-8 col-xl-6 col-xxl-4')
						->setDisabled(true)
						->formatValue(fn ($value, $entity) => $this->formatEntity($value, $entity))
		;
	}

	private function formatEntity($value, $entity) {
		return json_encode($value, \JSON_PRETTY_PRINT);
	}
}
