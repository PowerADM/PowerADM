<?php

namespace PowerADM\Logging;

use Doctrine\ORM\EntityManagerInterface;
use PowerADM\Entity\AuditLog;
use PowerADM\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class AuditLogger {
	public function __construct(private EntityManagerInterface $entityManager, private Security $security) {
	}

	public function log(array $entity, ?array $change, string $action): void {
		$auditLog = new AuditLog();
		$auditLog->setAction($action)
				 ->setEntity($entity)
				 ->setChangeSet($change ?? [])
				 ->setCreated(new \DateTimeImmutable())
		;

		$user = $this->security->getUser();
		if ($user instanceof User) {
			$auditLog->setUser($user);
		}

		$this->entityManager->persist($auditLog);
		$this->entityManager->flush();
	}
}
