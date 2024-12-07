<?php

namespace PowerADM\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PowerADM\Repository\TemplateRecordRepository;

#[ORM\Entity(repositoryClass: TemplateRecordRepository::class)]
class TemplateRecord implements ArrayExpressible {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\ManyToOne(inversedBy: 'templateRecords')]
	#[ORM\JoinColumn(nullable: false)]
	private ?Template $template = null;

	#[ORM\Column(length: 255)]
	private ?string $name = null;

	#[ORM\Column]
	private ?int $ttl = null;

	#[ORM\Column(length: 255)]
	private ?string $type = null;

	#[ORM\Column(type: Types::TEXT)]
	private ?string $content = null;

	#[ORM\Column(type: Types::TEXT, nullable: true)]
	private ?string $comment = null;

	public function getId(): ?int {
		return $this->id;
	}

	public function getTemplate(): ?Template {
		return $this->template;
	}

	public function setTemplate(?Template $template): static {
		$this->template = $template;

		return $this;
	}

	public function getName(): ?string {
		return $this->name;
	}

	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	public function getTtl(): ?int {
		return $this->ttl;
	}

	public function setTtl(int $ttl): static {
		$this->ttl = $ttl;

		return $this;
	}

	public function getType(): ?string {
		return $this->type;
	}

	public function setType(string $type): static {
		$this->type = $type;

		return $this;
	}

	public function getContent(): ?string {
		return $this->content;
	}

	public function setContent(string $content): static {
		$this->content = $content;

		return $this;
	}

	public function getComment(): ?string {
		return $this->comment;
	}

	public function setComment(?string $comment): static {
		$this->comment = $comment;

		return $this;
	}

	public function toArray(): array {
		return get_object_vars($this);
	}
}
