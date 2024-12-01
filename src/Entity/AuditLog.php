<?php

namespace PowerADM\Entity;

use Doctrine\ORM\Mapping as ORM;
use PowerADM\Repository\AuditLogRepository;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
class AuditLog {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(nullable: true)]
	private ?int $userID = null;

	#[ORM\Column]
	private ?\DateTimeImmutable $created = null;

	#[ORM\Column]
	private array $changeSet = [];

	#[ORM\Column]
	private array $entity = [];

	public function getId(): ?int {
		return $this->id;
	}

	public function getUserID(): ?int {
		return $this->userID;
	}

	public function setUserID(int $userID): static {
		$this->userID = $userID;

		return $this;
	}

	public function getCreated(): ?\DateTimeImmutable {
		return $this->created;
	}

	public function setCreated(\DateTimeImmutable $created): static {
		$this->created = $created;

		return $this;
	}

	public function getChangeSet(): array {
		return $this->changeSet;
	}

	public function setChangeSet(array $changeSet): static {
		$this->changeSet = $changeSet;

		return $this;
	}

	public function getEntity(): array {
		return $this->entity;
	}

	public function setEntity(array $entity): static {
		$this->entity = $entity;

		return $this;
	}
}
