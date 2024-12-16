<?php

namespace PowerADM\Controller\Zone;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use PowerADM\Entity\ForwardZone;

#[AdminCrud(routePath: '/zones/forward', routeName: 'forwardzones')]
class ForwardZoneCrudController extends AbstractZoneCrudController {
	public static function getEntityFqcn(): string {
		return ForwardZone::class;
	}

	public function configureCrud(Crud $crud): Crud {
		return parent::configureCrud($crud)
					->setEntityLabelInSingular('Forward Zone')
					->setEntityLabelInPlural('Forward Zones')
					->setPageTitle('detail', fn (ForwardZone $forwardZone) => \sprintf('Forward Zone - %s', $forwardZone->getName()))
					->setEntityPermission('FORWARD_ZONE_EDIT')
		;
	}

	public function index(AdminContext $context) {
		$this->pdnsProvider->syncZonesFromPDNS();

		return parent::index($context);
	}

	public function detail(AdminContext $context) {
		$responseParameters = parent::detail($context);
		$responseParameters->set('zoneType', 'forward');
		$responseParameters->set('zoneTypes', explode(',', $this->getParameter('forward_record_types')));

		return $responseParameters;
	}

	public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder {
		$queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
		if ($this->isGranted('ROLE_EDITOR')) {
			return $queryBuilder;
		}
		$queryBuilder->andWhere('entity.id IN (:zones)')
			->setParameter('zones', $this->getUser()->getAllowedForwardZones())
		;

		return $queryBuilder;
	}
}
