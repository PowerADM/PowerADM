<?php

namespace PowerADM\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use PowerADM\Entity\ForwardZone;
use PowerADM\Entity\ReverseZone;
use PowerADM\Entity\Template;
use PowerADM\Entity\User;
use PowerADM\Provider\PDNSProvider;
use PowerADM\Repository\ForwardZoneRepository;
use PowerADM\Repository\ReverseZoneRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController {
	public function __construct(private ForwardZoneRepository $forwardZoneRepository, private ReverseZoneRepository $reverseZoneRepository, private PDNSProvider $pdnsProvider) {
	}

	#[Route('/', name: 'padm')]
	public function index(): Response {
		$forwardZoneCount = $this->forwardZoneRepository->count([]);
		$reverseZoneCount = $this->reverseZoneRepository->count([]);
		$serverStatistics = $this->pdnsProvider->get()->statistics();
		$configuration = $this->pdnsProvider->getConnector()->get('config');

		return $this->render(
			'dashboard.html.twig',
			[
				'forwardZoneCount' => $forwardZoneCount,
				'reverseZoneCount' => $reverseZoneCount,
				'serverStatistics' => $serverStatistics,
				'configuration' => $configuration,
			]
		);
	}

	#[Route('/statistics', name: 'padm_statistics')]
	public function statistics(): Response {
		$forwardZoneCount = $this->forwardZoneRepository->count([]);
		$reverseZoneCount = $this->reverseZoneRepository->count([]);
		$serverStatistics = $this->pdnsProvider->get()->statistics();
		$configuration = $this->pdnsProvider->getConnector()->get('config');

		return $this->render(
			'dashboard.html.twig',
			[
				'forwardZoneCount' => $forwardZoneCount,
				'reverseZoneCount' => $reverseZoneCount,
				'serverStatistics' => $serverStatistics,
				'configuration' => $configuration,
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
			->setTitle('PowerADM')
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
			yield MenuItem::linkToUrl('Audit Log', 'fa fa-file-lines', '/audit-log');
			yield MenuItem::linkToUrl('PDNS configuration', 'fa fa-wrench', '/configuration');
			yield MenuItem::linkToUrl('Statistics', 'fa fa-chart-simple', '/statistics');
			yield MenuItem::linkToCrud('Templates', 'fa fa-copy', Template::class);
			yield MenuItem::linkToCrud('Users', 'fa fa-user', User::class);
			yield MenuItem::section();
			yield MenuItem::linkToUrl('Settings', 'fa fa-gear', '/settings');
		}
	}
}
