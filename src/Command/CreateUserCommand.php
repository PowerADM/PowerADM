<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

#[AsCommand(name: 'padm:user:create', description: 'Create a user.')]
final class CreateUserCommand extends Command {
	public function __construct(private PasswordHasherFactoryInterface $passwordHasherFactory, private EntityManagerInterface $entityManager) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setDefinition([
			new InputArgument('username', InputArgument::REQUIRED, 'The username'),
			new InputArgument('password', InputArgument::REQUIRED, 'The password'),
		])
			->setHelp('This command allows you to create a user.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$username = $input->getArgument('username');
		$password = $input->getArgument('password');

		$passwordHasher = $this->passwordHasherFactory->getPasswordHasher(User::class);
		$hashedPassword = $passwordHasher->hash($password);

		$user = new User();
		$user->setUsername($username);
		$user->setPassword($hashedPassword);
		$this->entityManager->persist($user);
		$this->entityManager->flush();

		$output->writeln(\sprintf('Created user <comment>%s</comment>', $username));

		return 0;
	}

	protected function interact(InputInterface $input, OutputInterface $output): void {
		$questions = [];

		if (!$input->getArgument('username')) {
			$question = new Question('Please choose a username:');
			$question->setValidator(function ($username) {
				if (empty($username)) {
					throw new \Exception('Username can not be empty');
				}

				return $username;
			});
			$questions['username'] = $question;
		}

		if (!$input->getArgument('password')) {
			$question = new Question('Please choose a password:');
			$question->setValidator(function ($password) {
				if (empty($password)) {
					throw new \Exception('Password can not be empty');
				}

				return $password;
			});
			$question->setHidden(true);
			$questions['password'] = $question;
		}

		$helper = $this->getHelper('question');
		\assert($helper instanceof QuestionHelper);

		foreach ($questions as $name => $question) {
			$answer = $helper->ask($input, $output, $question);
			$input->setArgument($name, $answer);
		}
	}
}
