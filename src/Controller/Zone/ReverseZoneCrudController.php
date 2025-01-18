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
use PowerADM\Entity\ReverseZone;

#[AdminCrud(routePath: '/zones/reverse', routeName: 'reversezone')]
class ReverseZoneCrudController extends AbstractZoneCrudController {
	public static function getEntityFqcn(): string {
		return ReverseZone::class;
	}

	public function configureCrud(Crud $crud): Crud {
		return parent::configureCrud($crud)
					->setEntityLabelInSingular('pdns.reverse_zone')
					->setEntityLabelInPlural('pdns.reverse_zones')
					->setPageTitle('detail', fn (ReverseZone $reverseZone) => \sprintf('Reverse Zone - %s', rtrim($reverseZone->getName(), '.')))
					->setEntityPermission('REVERSE_ZONE_EDIT')
		;
	}

	public function index(AdminContext $context) {
		$this->pdnsProvider->syncZonesFromPDNS(false);

		return parent::index($context);
	}

	public function detail(AdminContext $context) {
		$responseParameters = parent::detail($context);
		$responseParameters->set('zoneType', 'reverse');
		$responseParameters->set('zoneTypes', explode(',', $this->getParameter('reverse_record_types')));

		return $responseParameters;
	}

	public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder {
		$queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
		if ($this->isGranted('ROLE_EDITOR')) {
			return $queryBuilder;
		}
		$queryBuilder->andWhere('entity.id IN (:zones)')
			->setParameter('zones', $this->getUser()->getAllowedReverseZones())
		;

		return $queryBuilder;
	}
}
