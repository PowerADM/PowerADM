<?php

namespace App\Controller;

use App\Entity\ForwardZone;
use App\Entity\ReverseZone;
use App\Entity\Template;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractDashboardController {
	public function __construct(
		private ChartBuilderInterface $chartBuilder,
	) {
	}

	#[Route('/', name: 'padm')]
	public function index(): Response {
		$chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

		return $this->render('dashboard.html.twig', [
			'chart' => $chart,
		]);
	}

	public function configureDashboard(): Dashboard {
		return Dashboard::new()
			->setTitle('PowerADM')
			->setDefaultColorScheme('auto')
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
