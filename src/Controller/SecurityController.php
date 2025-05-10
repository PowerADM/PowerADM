<?php

namespace PowerADM\Controller;

use Drenso\OidcBundle\OidcClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController {

	public function __construct(
		#[Autowire('%env(OIDC_ENABLED)%')]
		private readonly bool $oidcEnabled
	){}

	#[Route(path: '/login', name: 'login')]
	public function login(AuthenticationUtils $authenticationUtils, TranslatorInterface $translator): Response {
		if ($this->getUser() !== null) {
			return $this->redirectToRoute('padm');
		}
		$error = $authenticationUtils->getLastAuthenticationError();
		$lastUsername = $authenticationUtils->getLastUsername();

		return $this->render('@EasyAdmin/page/login.html.twig', [
			'error' => $error,
			'last_username' => $lastUsername,
			'translation_domain' => 'admin',
			'page_title' => 'PowerADM Login',
			'favicon_path' => 'img/favicon.svg',
			'target_path' => $this->generateUrl('padm'),
			'username_label' => $translator->trans('pdns.login.username'),
			'password_label' => $translator->trans('pdns.login.password'),
			'sign_in_label' => $translator->trans('pdns.login.login'),
			'remember_me_enabled' => true,
			'oidcEnabled' => $this->oidcEnabled,
		]);
	}

	#[Route('/login_oidc', name: 'login_oidc')]
	#[IsGranted('PUBLIC_ACCESS')]
	public function oidcLogin(OidcClientInterface $oidcClient): RedirectResponse {
		return $oidcClient->generateAuthorizationRedirect(scopes: ['openid', 'profile', 'email', 'groups']);
	}

	#[Route(path: '/logout', name: 'logout')]
	public function logout(): void {
		throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
	}
}
