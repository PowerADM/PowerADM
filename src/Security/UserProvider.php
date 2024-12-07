<?php

namespace PowerADM\Security;

use Doctrine\ORM\EntityManagerInterface;
use Drenso\OidcBundle\Exception\OidcException;
use Drenso\OidcBundle\Model\OidcUserData;
use Drenso\OidcBundle\Security\UserProvider\OidcUserProviderInterface;
use PowerADM\Entity\User;
use PowerADM\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, OidcUserProviderInterface {
	public function __construct(private EntityManagerInterface $entityManager, private UserRepository $userRepository, private ParameterBagInterface $params) {
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
				$user->setFullname($userData->getUserDataString('name'));
				$user->setUsername($userIdentifier);
				$user->setRole($this->parseRoles($userData->getUserDataArray('groups')));
				$this->entityManager->persist($user);
			} else {
				if ($user->getFullname() !== $userData->getUserDataString('name')) {
					$user->setFullname($userData->getUserDataString('name'));
					$this->entityManager->persist($user);
				}
				$role = $this->parseRoles($userData->getUserDataArray('groups'));
				if ($user->getRole() !== $role) {
					$user->setRole($role);
					$this->entityManager->persist($user);
				}
			}
			$this->entityManager->flush();
		} catch (\Throwable $th) {
			throw new OidcException('Cannot create user', previous: $th);
		}
	}

	public function parseRoles(array $roles): string {
		if (\in_array($this->params->get('oidc_admin_role'), $roles)) {
			return 'ROLE_ADMIN';
		}
		if (\in_array($this->params->get('oidc_editor_role'), $roles)) {
			return 'ROLE_EDITOR';
		}
		if (\in_array($this->params->get('oidc_user_role'), $roles)) {
			return 'ROLE_USER';
		}

		throw new AccessDeniedHttpException('User does not have the required role');
	}

	public function loadOidcUser(string $userIdentifier): UserInterface {
		return $this->loadUserByIdentifier($userIdentifier);
	}
}
