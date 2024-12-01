<?php

namespace PowerADM\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PowerADM\Repository\TemplateRepository;

#[ORM\Entity(repositoryClass: TemplateRepository::class)]
class Template implements ArrayExpressible {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(length: 255)]
	private ?string $name = null;

	#[ORM\Column(type: Types::TEXT, nullable: true)]
	private ?string $description = null;

	#[ORM\Column(nullable: true)]
	private ?array $records = null;

	public function getId(): ?int {
		return $this->id;
	}

	public function getName(): ?string {
		return $this->name;
	}

	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	public function getDescription(): ?string {
		return $this->description;
	}

	public function setDescription(?string $description): static {
		$this->description = $description;

		return $this;
	}

	public function getRecords(): ?array {
		return $this->records;
	}

	public function setRecords(?array $records): static {
		$this->records = $records;

		return $this;
	}

	public function toArray(): array {
		return get_object_vars($this);
	}
}
