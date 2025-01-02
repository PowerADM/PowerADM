<?php

namespace PowerADM\Entity;

use Doctrine\ORM\Mapping as ORM;
use PowerADM\Repository\ReverseZoneRepository;

#[ORM\Entity(repositoryClass: ReverseZoneRepository::class)]
class ReverseZone implements ArrayExpressible {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(length: 255)]
	private ?string $name = null;

	#[ORM\Column(length: 8)]
	private ?string $type = null;

	#[ORM\Column(nullable: true)]
	private ?int $serial = null;

	public ?int $template = null;

	public function getId(): ?int {
		return $this->id;
	}

	public function getName(): ?string {
		return $this->name;
	}

	public function setName(string $name): static {
		$this->name = $name;
		if (!str_ends_with($name, '.')) {
			$this->name .= '.';
		}

		return $this;
	}

	public function getType(): ?string {
		return $this->type;
	}

	public function setType(string $type): static {
		$this->type = $type;

		return $this;
	}

	public function getSerial(): ?int {
		return $this->serial;
	}

	public function setSerial(?int $serial): static {
		$this->serial = $serial;

		return $this;
	}

	public function getTemplate(): ?int {
		return $this->template;
	}

	public function toArray(): array {
		$array = get_object_vars($this);
		$array['entityType'] = 'reverseZone';

		return $array;
	}
}
