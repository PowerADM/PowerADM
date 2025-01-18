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
				->setEntityLabelInSingular('pdns.audit_log.audit_log')
				->setEntityLabelInPlural('pdns.audit_log.audit_log')
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
						->formatValue(fn ($value, $entity) => $entity->getUser()?->getFullName() ?: $entity->getUser()?->getUsername())
		;
		yield ChoiceField::new('action')
						->setColumns('col-8 col-xl-6 col-xxl-4')
						->setDisabled(true)
						->setChoices([
							'pdns.audit_log.create' => 'CREATE',
							'pdns.audit_log.update' => 'UPDATE',
							'pdns.audit_log.delete' => 'DELETE',
						])->renderAsBadges([
							'CREATE' => 'primary',
							'UPDATE' => 'warning',
							'DELETE' => 'danger',
						])->renderExpanded()
		;
		yield ArrayField::new('entityArray')
						->setColumns('col-8 col-xl-6 col-xxl-4')
						->setDisabled(true)
						->setVirtual(true)
						->formatValue(fn ($value, $entity) => $this->formatEntity($value, $entity))
		;
		yield ArrayField::new('changeSetArray')
						->setColumns('col-8 col-xl-6 col-xxl-4')
						->setDisabled(true)
						->setVirtual(true)
						->formatValue(fn ($value, $entity) => $this->formatChange($value, $entity))
		;
	}

	private function formatEntity($value, $entity) {
		$id = '';
		if ($value['id']) {
			$id = ' #'.$value['id'];
		}
		switch ($value['entityType']) {
			case 'user':
				return 'User'.$id.' ('.($value['fullname'] ?: $value['username']).')';
			case 'forwardZone':
				return 'Forward Zone'.$id.' ('.$value['name'].')';
			case 'reverseZone':
				return 'Reverse Zone'.$id.' ('.$value['name'].')';
			case 'template':
				return 'Template'.$id.' ('.$value['name'].')';
			default:
				return '';
		}

		return json_encode($value, \JSON_PRETTY_PRINT);
	}

	private function formatChange($value, $entity) {
		return json_encode($value, \JSON_PRETTY_PRINT);
	}
}
