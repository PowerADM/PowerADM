<?php

namespace PowerADM\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'padm:setup', description: 'PowerADM Setup')]
class SetupCommand extends Command {
	public function __construct(
		#[Autowire('%kernel.secret%')]
		#[\SensitiveParameter]
		private readonly ?string $appSecret
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setHelp('This command is used to setup PowerADM.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		if (!$this->appSecret || $this->appSecret === 'ThisTokenIsNotSoSecretChangeIt') {
			$output->writeln('Generating APP_SECRET...');
			$secret = bin2hex(random_bytes(32));
			if (file_exists('.env.local')) {
				$env = file_get_contents('.env.local');
			} else {
				$env = file_get_contents('.env.local.dist');
			}
			$lines = explode("\n", $env);
			if (str_contains($env, 'APP_SECRET')) {
				foreach ($lines as $key => $line) {
					if (str_contains($line, 'APP_SECRET')) {
						$lines[$key] = 'APP_SECRET='.$secret;
					}
				}
			} else {
				$lines[] = 'APP_SECRET='.$secret;
			}
			file_put_contents('.env.local', implode("\n", $lines));
		} else {
			$output->writeln('APP_SECRET already set.');
		}
		$output->writeln('Setup complete!');

		return 0;
	}
}
