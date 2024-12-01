<?php

namespace PowerADM\Security;

use Doctrine\ORM\EntityManagerInterface;
use Drenso\OidcBundle\Exception\OidcException;
use Drenso\OidcBundle\Model\OidcUserData;
use Drenso\OidcBundle\Security\UserProvider\OidcUserProviderInterface;
use PowerADM\Entity\User;
use PowerADM\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, OidcUserProviderInterface {
	public function __construct(private EntityManagerInterface $entityManager, private UserRepository $userRepository) {
	}

	public function loadUserByIdentifier(string $identifier): UserInterface {
		$user = $this->userRepository->findOneBy(['username' => $identifier]);
		if (!$user) {
			throw new UserNotFoundException('User with id "%s" not found');
		}

		return $user;
	}

	public function refreshUser(UserInterface $user): UserInterface {
		if (!$user instanceof User) {
			throw new UnsupportedUserException(\sprintf('Invalid user class "%s".', $user::class));
		}

		$refreshedUser = $this->userRepository->find($user->getId());
		if (!$refreshedUser) {
			throw new UserNotFoundException(\sprintf('User with id "%s" not found', $user->getId()));
		}

		return $refreshedUser;
	}

	public function supportsClass(string $class): bool {
		return $class === User::class || is_subclass_of($class, User::class);
	}

	public function ensureUserExists(string $userIdentifier, OidcUserData $userData): void {
		try {
			$user = $this->userRepository->findOneBy(['username' => $userIdentifier]);
			if (!$user) {
				$user = new User();
				$user->setUsername($userIdentifier);
				$user->setRole($this->parseRoles($userData->getUserDataArray('groups')));
				$this->entityManager->persist($user);
			}
			$this->entityManager->flush();
		} catch (\Throwable $th) {
			throw new OidcException('Cannot create user', previous: $th);
		}
	}

	public function parseRoles(array $roles): string {
		foreach ($roles as $role) {
			if (getenv('OIDC_ADMIN_ROLE', true) === $role) {
				return 'ROLE_ADMIN';
			}
			if (getenv('OIDC_EDITOR_ROLE', true) === $role) {
				return 'ROLE_ADMIN';
			}
			if (getenv('OIDC_USER_ROLE', true) === $role) {
				return 'ROLE_USER';
			}
		}

		return '';
	}

	public function loadOidcUser(string $userIdentifier): UserInterface {
		return $this->loadUserByIdentifier($userIdentifier);
	}
}
