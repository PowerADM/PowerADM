<?php

namespace PowerADM\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PowerADM\Repository\TemplateRepository;

#[ORM\Entity(repositoryClass: TemplateRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_NAME', fields: ['name'])]
class Template implements ArrayExpressible {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(length: 255)]
	private ?string $name = null;

	#[ORM\Column(type: Types::TEXT, nullable: true)]
	private ?string $description = null;

	/**
	 * @var Collection<int, TemplateRecord>
	 */
	#[ORM\OneToMany(targetEntity: TemplateRecord::class, mappedBy: 'template', orphanRemoval: true)]
	private Collection $templateRecords;

	public function __construct() {
		$this->templateRecords = new ArrayCollection();
	}

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

	public function toArray(): array {
		$array = get_object_vars($this);
		unset($array['templateRecords']);
		$array['entityType'] = 'template';

		return $array;
	}

	public function getTemplateRecords(): Collection {
		return $this->templateRecords;
	}

	public function addTemplateRecord(TemplateRecord $templateRecord): static {
		if (!$this->templateRecords->contains($templateRecord)) {
			$this->templateRecords->add($templateRecord);
			$templateRecord->setTemplate($this);
		}

		return $this;
	}

	public function removeTemplateRecord(TemplateRecord $templateRecord): static {
		if ($this->templateRecords->removeElement($templateRecord)) {
			if ($templateRecord->getTemplate() === $this) {
				$templateRecord->setTemplate(null);
			}
		}

		return $this;
	}
}
