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

	#[ORM\Column(length: 32)]
	private ?string $action = null;

	#[ORM\ManyToOne]
	private ?User $user = null;

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

	public function getChangeSetArray(): array {
		$changeSet = [];

		return [json_encode($this->changeSet)];
	}

	public function getEntity(): array {
		return $this->entity;
	}

	public function setEntity(array $entity): static {
		$this->entity = $entity;

		return $this;
	}

	public function getEntityArray(): array {
		$entity = [];
		foreach ($this->entity as $key => $value) {
			if (\is_array($value)) {
				$entity[$key] = '';
				continue;
			}
			$entity[$key] = $value;
		}

		return $entity;
	}

	public function getAction(): ?string {
		return $this->action;
	}

	public function setAction(string $action): static {
		$this->action = $action;

		return $this;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser(?User $user): static {
		$this->user = $user;

		return $this;
	}
}
