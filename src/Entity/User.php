<?php

namespace PowerADM\Entity;

use Doctrine\ORM\Mapping as ORM;
use PowerADM\Repository\UserRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface, ArrayExpressible {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(length: 180)]
	private ?string $username = null;

	#[ORM\Column]
	private string $role = 'ROLE_USER';

	#[ORM\Column(nullable: true)]
	private ?string $password = null;

	#[ORM\Column(nullable: true)]
	private ?array $allowed_forward_zones = null;

	#[ORM\Column(nullable: true)]
	private ?array $allowed_reverse_zones = null;

	#[ORM\Column(length: 255, nullable: true)]
	private ?string $fullname = null;

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
		return [$this->getRole()];
	}

	public function getRole(): string {
		return $this->role ?: 'ROLE_USER';
	}

	public function setRole(string $role): static {
		if (\in_array($role, ['ROLE_ADMIN', 'ROLE_EDITOR', 'ROLE_USER'])) {
			$this->role = $role;
		}

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

	public function getAllowedForwardZones(): array {
		return $this->allowed_forward_zones ?? [];
	}

	public function setAllowedForwardZones(?array $allowed_forward_zones): static {
		$this->allowed_forward_zones = $allowed_forward_zones;

		return $this;
	}

	public function getAllowedReverseZones(): array {
		return $this->allowed_reverse_zones ?? [];
	}

	public function setAllowedReverseZones(?array $allowed_reverse_zones): static {
		$this->allowed_reverse_zones = $allowed_reverse_zones;

		return $this;
	}

	public function getFullname(): ?string {
		return $this->fullname;
	}

	public function setFullname(?string $fullname): static {
		$this->fullname = $fullname;

		return $this;
	}

	public function toArray() {
		return get_object_vars($this);
	}
}
