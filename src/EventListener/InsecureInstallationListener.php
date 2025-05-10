<?php

namespace PowerADM\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener]
class InsecureInstallationListener {
	public function __construct(
		#[Autowire('%kernel.secret%')]
		#[\SensitiveParameter]
		private readonly string $appSecret,
	) {
	}

	public function __invoke(RequestEvent $event): void {
		$request = $event->getRequest();

		if ($request->getBasePath() !== '') {
			throw new \Exception('Your installation is not secure. Please set the document root to the <comment>public</comment> subfolder.');
		}

		if (!$this->appSecret || $this->appSecret === 'ThisTokenIsNotSoSecretChangeIt') {
			throw new \Exception('Your installation is not secure. Please set the "APP_SECRET" in your .env.local.');
		}
	}
}
