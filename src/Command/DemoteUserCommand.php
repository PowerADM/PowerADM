<?php

namespace PowerADM\Command;

use PowerADM\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'padm:user:demote', description: 'Remove a role from a user')]
class DemoteUserCommand extends Command {
	public const VALID_ROLES = ['ROLE_ADMIN', 'ROLE_EDITOR'];

	public function __construct(private UserRepository $userRepository) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setDefinition([
			new InputArgument('username', InputArgument::REQUIRED, 'The username'),
			new InputArgument('role', InputArgument::REQUIRED, 'The role to remove'),
		])
			->setHelp('This command allows you to remove a role from a user.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$username = $input->getArgument('username');
		$role = strtoupper($input->getArgument('role'));

		if (!\in_array($role, self::VALID_ROLES)) {
			$output->writeln(\sprintf('Invalid role <comment>%s</comment>. Valid roles are: <comment>%s</comment>', $role, implode(', ', self::VALID_ROLES)));

			return Command::FAILURE;
		}

		$user = $this->userRepository->findOneBy(['username' => $username]);
		if (!$user) {
			$output->writeln(\sprintf('User <comment>%s</comment> not found', $username));

			return Command::FAILURE;
		}

		$roles = $user->getRoles();
		if (!\in_array($role, $roles)) {
			$output->writeln(\sprintf('User <comment>%s</comment> does not have role <comment>%s</comment>', $username, $role));

			return Command::FAILURE;
		}

		$roles = array_diff($roles, [$role]);
		$user->setRoles($roles);
		$this->userRepository->getEntityManager()->persist($user);
		$this->userRepository->getEntityManager()->flush();

		$output->writeln(\sprintf('Removed role <comment>%s</comment> from user <comment>%s</comment>', $role, $username));

		return Command::SUCCESS;
	}
}
