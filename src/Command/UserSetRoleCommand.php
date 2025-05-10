<?php

namespace PowerADM\Command;

use Doctrine\ORM\EntityManagerInterface;
use PowerADM\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'padm:user:set-role', description: 'Set the role of a user')]
class UserSetRoleCommand extends Command {
	public function __construct(private UserRepository $userRepository, private EntityManagerInterface $entityManager) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setDefinition([
			new InputArgument('username', InputArgument::REQUIRED, 'The username'),
			new InputArgument('role', InputArgument::REQUIRED, 'The role to set'),
		])
			->setHelp('This command allows you to set the role of a user.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$username = $input->getArgument('username');
		$role = strtoupper($input->getArgument('role'));

		$user = $this->userRepository->findOneBy(['username' => $username]);
		if (!$user) {
			$output->writeln(\sprintf('User <comment>%s</comment> not found', $username));

			return Command::FAILURE;
		}

		if (!\in_array($role, ['ROLE_USER', 'ROLE_EDITOR', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'], true)) {
			$output->writeln(\sprintf('Invalid role <comment>%s</comment>', $role));
			$output->writeln('Valid roles are: ROLE_USER, ROLE_EDITOR, ROLE_ADMIN, ROLE_SUPER_ADMIN');

			return Command::FAILURE;
		}

		$user->setRole($role);
		$this->entityManager->persist($user);
		$this->entityManager->flush();

		$output->writeln(\sprintf('Set role <comment>%s</comment> for user <comment>%s</comment>', $role, $username));

		return 0;
	}
}
