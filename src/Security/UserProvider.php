<?php

namespace PowerADM\Security;

use Doctrine\ORM\EntityManagerInterface;
use Drenso\OidcBundle\Exception\OidcException;
use Drenso\OidcBundle\Model\OidcUserData;
use Drenso\OidcBundle\Security\UserProvider\OidcUserProviderInterface;
use PowerADM\Entity\User;
use PowerADM\Repository\UserRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, OidcUserProviderInterface, LoggerAwareInterface {
	protected UserRepository $repo;

	public function __construct(private EntityManagerInterface $entityManager) {
		$this->repo = $entityManager->getRepository(User::class);
	}

	private LoggerInterface $logger;

	public function setLogger(LoggerInterface $logger): void {
		$this->logger = $logger;
	}

	public function loadUserByIdentifier(string $identifier): UserInterface {
		$user = $this->repo->findOneBy(['username' => $identifier]);
		if (!$user) {
			throw new UserNotFoundException('User with id "%s" not found');
		}

		return $user;
	}

	public function refreshUser(UserInterface $user): UserInterface {
		if (!$user instanceof User) {
			throw new UnsupportedUserException(\sprintf('Invalid user class "%s".', $user::class));
		}

		$refreshedUser = $this->repo->find($user->getId());
		if (!$user) {
			throw new UserNotFoundException(\sprintf('User with id "%s" not found', $user->getId()));
		}

		return $refreshedUser;
	}

	public function supportsClass(string $class): bool {
		return $class === User::class || is_subclass_of($class, User::class);
	}

	public function ensureUserExists(string $userIdentifier, OidcUserData $userData): void {
		try {
			$user = $this->repo->findOneBy(['username' => $userIdentifier]);
			if (!$user) {
				$user = new User();
				$user->setUsername($userIdentifier);
				$this->entityManager->persist($user);
			}
			$this->entityManager->flush();
		} catch (\Throwable $th) {
			throw new OidcException('cannot create user', previous: $th);
		}
	}

	public function loadOidcUser(string $userIdentifier): UserInterface {
		return $this->loadUserByIdentifier($userIdentifier);
	}
}
