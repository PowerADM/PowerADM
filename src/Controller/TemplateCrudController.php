<?php

namespace PowerADM\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
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
				->overrideTemplate('crud/detail', 'template_detail.html.twig')
		;
	}

	public function detail(AdminContext $context) {
		$template = $context->getEntity()->getInstance();
		$responseParameters = parent::detail($context);
		$responseParameters->set('zone', $template->getId());
		$responseParameters->set('zoneType', 'template');
		$responseParameters->set('zoneTypes', array_unique(explode(',', $this->getParameter('forward_record_types').','.$this->getParameter('reverse_record_types'))));
		$responseParameters->set('records', $template->getTemplateRecords());

		return $responseParameters;
	}

	public function configureActions(Actions $actions): Actions {
		return $actions
			->disable(Action::BATCH_DELETE)
			->add(Crud::PAGE_INDEX, Action::DETAIL)
		;
	}
}
