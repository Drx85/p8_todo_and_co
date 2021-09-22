<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;


class LoginFormAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function supports(Request $request): ?bool
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }
	
	public function authenticate(Request $request): PassportInterface
	{
		$credentials = [
			'username' => $request->request->get('username'),
			'password' => $request->request->get('password'),
			'csrf_token' => $request->request->get('_csrf_token'),
		];
		
		$request->getSession()->set(
			Security::LAST_USERNAME,
			$credentials['username']
		);
		return new Passport(
			new UserBadge($credentials['username']),
			new PasswordCredentials($credentials['password']),
			[new CsrfTokenBadge('authenticate', $credentials['csrf_token'])]
		);
	}

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }
	
		return new RedirectResponse($this->urlGenerator->generate('homepage'));
    }
	
	public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
	{
			return new RedirectResponse($this->urlGenerator->generate('invalid-credentials'));
	}
}
