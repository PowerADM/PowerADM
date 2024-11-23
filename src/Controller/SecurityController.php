<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController {
	#[Route(path: '/login', name: 'login')]
	public function login(AuthenticationUtils $authenticationUtils): Response {
		$error = $authenticationUtils->getLastAuthenticationError();
		$lastUsername = $authenticationUtils->getLastUsername();

		return $this->render('@EasyAdmin/page/login.html.twig', [
			'error' => $error,
			'last_username' => $lastUsername,
			'translation_domain' => 'admin',
			'page_title' => 'PowerADM Login',
			'target_path' => $this->generateUrl('padm'),
			'username_label' => 'Your username',
			'password_label' => 'Your password',
			'sign_in_label' => 'Log in',
			'remember_me_enabled' => true,
		]);
	}

	#[Route(path: '/logout', name: 'logout')]
	public function logout(): void {
		throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
	}
}
