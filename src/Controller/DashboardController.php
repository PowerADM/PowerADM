<?php

namespace PowerADM\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use PowerADM\Entity\AuditLog;
use PowerADM\Entity\ForwardZone;
use PowerADM\Entity\ReverseZone;
use PowerADM\Entity\Template;
use PowerADM\Entity\User;
use PowerADM\Provider\PDNSProvider;
use PowerADM\Repository\ForwardZoneRepository;
use PowerADM\Repository\ReverseZoneRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController {
	public function __construct(private ForwardZoneRepository $forwardZoneRepository, private ReverseZoneRepository $reverseZoneRepository, private PDNSProvider $pdnsProvider, private RequestStack $requestStack) {
	}

	#[Route('/', name: 'padm')]
	public function index(): Response {
		$forwardZoneCount = $this->forwardZoneRepository->count([]);
		$reverseZoneCount = $this->reverseZoneRepository->count([]);

		return $this->render(
			'dashboard.html.twig',
			[
				'forwardZoneCount' => $forwardZoneCount,
				'reverseZoneCount' => $reverseZoneCount,
			]
		);
	}

	#[Route('/configuration', name: 'padm_configuration')]
	public function configuration(): Response {
		$this->requestStack->getCurrentRequest()->attributes->set(EA::ROUTE_CREATED_BY_EASYADMIN, true);
		$configuration = $this->pdnsProvider->getConnector()->get('config');

		return $this->render(
			'pdns_config.html.twig',
			[
				'configuration' => $configuration,
			]
		);
	}

	#[Route('/statistics', name: 'padm_statistics')]
	public function statistics(): Response {
		$this->requestStack->getCurrentRequest()->attributes->set(EA::ROUTE_CREATED_BY_EASYADMIN, true);
		$serverStatistics = $this->pdnsProvider->get()->statistics();

		return $this->render(
			'pdns_stats.html.twig',
			[
				'serverStatistics' => $serverStatistics,
			]
		);
	}

	public function configureAssets(): Assets {
		return Assets::new()
				->addCssFile('css/style.css')
				->addAssetMapperEntry('app')
		;
	}

	public function configureDashboard(): Dashboard {
		return Dashboard::new()
			->setTitle("<img src='/img/logo.svg' alt='PowerADM Logo' class='pe-3 only-light'><img src='/img/logo-white.svg' alt='PowerADM Logo' class='pe-3 only-dark'>")
			->setFaviconPath('img/favicon.svg')
			->setDefaultColorScheme('auto')
			->generateRelativeUrls(true)
		;
	}

	public function configureMenuItems(): iterable {
		yield MenuItem::linkToUrl('Dashboard', 'fa fa-home', $this->generateUrl('padm'));
		yield MenuItem::section();
		yield MenuItem::linkToCrud('Forward Zones', 'fa fa-arrow-right', ForwardZone::class);
		yield MenuItem::linkToCrud('Reverse Zones', 'fa fa-arrow-left', ReverseZone::class);
		if ($this->isGranted('ROLE_ADMIN')) {
			yield MenuItem::section();
			yield MenuItem::linkToCrud('Audit Log', 'fa fa-file-lines', AuditLog::class);
			yield MenuItem::linkToUrl('PDNS configuration', 'fa fa-wrench', '/configuration');
			yield MenuItem::linkToUrl('Statistics', 'fa fa-chart-simple', '/statistics');
			yield MenuItem::linkToCrud('Templates', 'fa fa-copy', Template::class);
			yield MenuItem::linkToCrud('Users', 'fa fa-user', User::class);
		}
	}
}
