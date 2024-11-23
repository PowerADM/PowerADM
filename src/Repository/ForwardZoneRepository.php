<?php

namespace PowerADM\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PowerADM\Entity\ForwardZone;

/**
 * @extends ServiceEntityRepository<ForwardZone>
 */
class ForwardZoneRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, ForwardZone::class);
	}

	//    /**
	//     * @return ForwardZone[] Returns an array of ForwardZone objects
	//     */
	//    public function findByExampleField($value): array
	//    {
	//        return $this->createQueryBuilder('f')
	//            ->andWhere('f.exampleField = :val')
	//            ->setParameter('val', $value)
	//            ->orderBy('f.id', 'ASC')
	//            ->setMaxResults(10)
	//            ->getQuery()
	//            ->getResult()
	//        ;
	//    }

	//    public function findOneBySomeField($value): ?ForwardZone
	//    {
	//        return $this->createQueryBuilder('f')
	//            ->andWhere('f.exampleField = :val')
	//            ->setParameter('val', $value)
	//            ->getQuery()
	//            ->getOneOrNullResult()
	//        ;
	//    }
}
