<?php

namespace PowerADM\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PowerADM\Entity\ForwardZone;

class ForwardZoneRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, ForwardZone::class);
	}
}
