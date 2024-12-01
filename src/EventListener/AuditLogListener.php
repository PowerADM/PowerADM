<?php

namespace PowerADM\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use PowerADM\Entity\AuditLog;
use PowerADM\Logging\AuditLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

#[AsDoctrineListener(event: Events::prePersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::preRemove, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::preUpdate, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::postRemove, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::postUpdate, priority: 500, connection: 'default')]
class AuditLogListener {
	private ArrayAdapter $arrayAdapter;

	public function __construct(private AuditLogger $auditLogger) {
		$this->arrayAdapter = new ArrayAdapter();
	}

	public function prePersist(PrePersistEventArgs $args): void {
		if ($args->getObject() instanceof AuditLog) {
			return;
		}
		$entity = $args->getObject();

		$this->arrayAdapter->get('id'.$entity->getId(), fn () => $entity->toArray());
	}

	public function postPersist(PostPersistEventArgs $args): void {
		if ($args->getObject() instanceof AuditLog) {
			return;
		}
		$entity = $args->getObject();
		$id = $entity->getId();
		$changeSet = $this->arrayAdapter->getItem('id'.$id)->get();
		$this->auditLogger->log($entity, $changeSet);
	}

	public function preRemove(PreRemoveEventArgs $args): void {
		if ($args->getObject() instanceof AuditLog) {
			return;
		}
		$entity = $args->getObject();

		$this->arrayAdapter->get('id'.$entity->getId(), fn () => []);
	}

	public function postRemove(PostRemoveEventArgs $args): void {
		if ($args->getObject() instanceof AuditLog) {
			return;
		}
		$entity = $args->getObject();
		$id = $entity->getId();
		$changeSet = $this->arrayAdapter->getItem('id'.$id)->get();
		$this->auditLogger->log($entity, $changeSet);
	}

	public function preUpdate(PreUpdateEventArgs $args): void {
		if ($args->getObject() instanceof AuditLog) {
			return;
		}
		$entity = $args->getObject();

		$this->arrayAdapter->get('id'.$entity->getId(), fn () => $args->getEntityChangeSet());
	}

	public function postUpdate(PostUpdateEventArgs $args): void {
		if ($args->getObject() instanceof AuditLog) {
			return;
		}
		$entity = $args->getObject();
		$id = $entity->getId();
		$changeSet = $this->arrayAdapter->getItem('id'.$id)->get();
		$this->auditLogger->log($entity, $changeSet);
	}
}
