<?php

namespace PowerADM\Entity;

use Doctrine\ORM\Mapping as ORM;
use PowerADM\Repository\UserRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(length: 180)]
	private ?string $username = null;

	#[ORM\Column]
	private array $roles = [];

	#[ORM\Column(nullable: true)]
	private ?string $password = null;

	public function getId(): ?int {
		return $this->id;
	}

	public function getUsername(): ?string {
		return $this->username;
	}

	public function setUsername(string $username): static {
		$this->username = $username;

		return $this;
	}

	public function getUserIdentifier(): string {
		return (string) $this->username;
	}

	public function getRoles(): array {
		$roles = $this->roles;
		$roles[] = 'ROLE_USER';

		return array_unique($roles);
	}

	public function setRoles(array $roles): static {
		$this->roles = $roles;

		return $this;
	}

	public function getPassword(): ?string {
		return $this->password;
	}

	public function setPassword(string $password): static {
		$this->password = $password;

		return $this;
	}

	public function eraseCredentials(): void {
	}
}
