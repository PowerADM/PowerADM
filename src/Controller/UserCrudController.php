<?php

namespace PowerADM\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PowerADM\Entity\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class UserCrudController extends AbstractCrudController {
	public function __construct(private PasswordHasherFactoryInterface $passwordHasherFactory) {
	}

	public static function getEntityFqcn(): string {
		return User::class;
	}

	public function configureCrud(Crud $crud): Crud {
		return $crud
				->setEntityLabelInSingular('User')
				->setEntityLabelInPlural('User')
				->setSearchFields(['username'])
				->renderContentMaximized()
				->showEntityActionsInlined(true)
				->setEntityPermission('ROLE_ADMIN')
		;
	}

	public function configureActions(Actions $actions): Actions {
		return $actions
			->disable(Action::BATCH_DELETE)
		;
	}

	public function configureFields(string $pageName): iterable {
		yield IdField::new('id')->onlyOnIndex();
		yield TextField::new('fullName')
						->setColumns('col-8 col-xl-6 col-xxl-4')
		;
		yield FormField::addRow();
		yield TextField::new('username')
						->setColumns('col-8 col-xl-6 col-xxl-4')
						->setDisabled($pageName === Crud::PAGE_EDIT)
						->setHelp(match ($pageName) {
							Crud::PAGE_NEW => 'The username can\'t be changed after the user is created.',
							Crud::PAGE_EDIT => 'The username can\'t be changed after the user is created.',
							default => '',
						})
		;
		yield FormField::addRow();
		yield ChoiceField::new('role')
						->setChoices([
							'User' => 'ROLE_USER',
							'Editor' => 'ROLE_EDITOR',
							'Admin' => 'ROLE_ADMIN',
						])->renderAsBadges([
							'ROLE_USER' => 'primary',
							'ROLE_EDITOR' => 'warning',
							'ROLE_ADMIN' => 'danger',
						])->renderExpanded()
		;
		yield FormField::addRow();
		yield AssociationField::new('allowed_forward_zones')
				->setFormTypeOption('choice_label', 'name');
		yield AssociationField::new('allowed_reverse_zones')
				->setFormTypeOption('choice_label', 'name');

		yield FormField::addRow();
		yield TextField::new('password')
						->setColumns('col-8 col-xl-6 col-xxl-4')
						->setHelp(match ($pageName) {
							Crud::PAGE_EDIT => 'Leave empty if you don\'t want to change the password.',
							default => '',
						})
						->setFormTypeOptions([
							'mapped' => false,
						])
						->setRequired($pageName === Crud::PAGE_NEW)
						->onlyOnForms()
		;
	}

	public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface {
		$formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);

		return $this->addPasswordEventListener($formBuilder);
	}

	public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface {
		$formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);

		return $this->addPasswordEventListener($formBuilder);
	}

	private function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface {
		return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());
	}

	private function hashPassword() {
		return function ($event): void {
			$form = $event->getForm();
			if (!$form->isValid()) {
				return;
			}
			$password = $form->get('password')->getData();
			if ($password === null) {
				return;
			}

			$hash = $this->passwordHasherFactory->getPasswordHasher(User::class)->hash($password);
			$form->getData()->setPassword($hash);
		};
	}
}
