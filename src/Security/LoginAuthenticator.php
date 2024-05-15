<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'visitor_authentication_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        // 2- Récupération de l'email envoyé par l'utilisateur depuis le formulaire de connexion
        $email = $request->getPayload()->getString('email');

        // 3- Sauvegarde de l'email en session 
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // 4- Vérification de la correspondance de l'email et du mot de passe de l'utilisateur provenant du formulaire à un utilisateur en BDD
        return new Passport(
            new UserBadge($email), // Vérification que l'email existe en BDD (ex: $user = "SELECT * FROM user WHERE email=:email")
            new PasswordCredentials($request->getPayload()->getString('password')), // Vérification que le mot de passe soit correct (ex: password_verify())
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')), // Vérification du token de controle contre les failles CSRF
                new RememberMeBadge(), // Vérification si l'utilisateur souhaite qu'on ce souvienne de lui grace au COOKIE
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // 5- Si l'utilisateur n'existe pas en BDD, récupérer l'email précedemment envoyer depuis le formulaire et qui a été sauvegardé en session et ensuite effectuer la redirection vers la page de laquelle proviennent les informations.
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }
        
        // 6- Dans le cas contraire, effectuer une redirection vers la page d'accueil
        return new RedirectResponse($this->urlGenerator->generate('visitor_welcome_index'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
