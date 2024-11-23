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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController {
	#[Route('/', name: 'padm')]
	public function index(): Response {
		return $this->render('dashboard.html.twig');
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
		return [
			MenuItem::linkToUrl('Dashboard', 'fa fa-home', $this->generateUrl('padm')),
			MenuItem::section(),
			MenuItem::linkToCrud('Forward Zones', 'fa fa-arrow-right', ForwardZone::class),
			MenuItem::linkToCrud('Reverse Zones', 'fa fa-arrow-left', ReverseZone::class),
			MenuItem::section(),
			MenuItem::linkToCrud('Templates', 'fa fa-copy', Template::class),
			MenuItem::linkToCrud('Users', 'fa fa-user', User::class),
		];
	}
}
