<?php

namespace PowerADM\Logging;

use Doctrine\ORM\EntityManagerInterface;
use PowerADM\Entity\AuditLog;
use PowerADM\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class AuditLogger {
	public function __construct(private EntityManagerInterface $entityManager, private Security $security) {
	}

	public function log(object $object, ?array $change): void {
		$auditLog = new AuditLog();
		$auditLog->setChangeSet($change ?? $object->toArray());
		$auditLog->setCreated(new \DateTimeImmutable());
		$user = $this->security->getUser();
		if ($user instanceof User) {
			$auditLog->setUserId($user->getId());
		}

		$this->entityManager->persist($auditLog);
		$this->entityManager->flush();
	}
}
