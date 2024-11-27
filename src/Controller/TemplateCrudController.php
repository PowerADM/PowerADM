<?php

namespace PowerADM\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PowerADM\Entity\Template;

class TemplateCrudController extends AbstractCrudController {
	public static function getEntityFqcn(): string {
		return Template::class;
	}

	public function configureCrud(Crud $crud): Crud {
		return $crud
				->setEntityLabelInSingular('Template')
				->setEntityLabelInPlural('Templates')
				->setSearchFields(['name', 'description'])
				->renderContentMaximized()
				->showEntityActionsInlined(true)
				->setEntityPermission('ROLE_ADMIN')
		;
	}

	public function configureActions(Actions $actions): Actions {
		return $actions
			->disable(Action::BATCH_DELETE)
			->add(Crud::PAGE_INDEX, Action::DETAIL)
		;
	}
}
