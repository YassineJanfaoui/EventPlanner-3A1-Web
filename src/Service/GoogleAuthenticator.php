<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class GoogleAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    private OAuth2ClientInterface $client;
    private EntityManagerInterface $em;
    private RouterInterface $router;

    public function __construct(GoogleClient $client, EntityManagerInterface $em, RouterInterface $router)
    {
        $this->client = $client;
        $this->em = $em;
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $accessToken = $this->client->getAccessToken();
        /** @var GoogleUser $googleUser */
        $googleUser = $this->client->fetchUserFromToken($accessToken);

        $email = $googleUser->getEmail();

        return new SelfValidatingPassport(
            new UserBadge($email, function (string $userIdentifier) use ($googleUser) {
                $user = $this->em->getRepository(User::class)->findOneBy(['email' => $userIdentifier]);

                if (!$user) {
                    $user = new User();
                    $user->setEmail($googleUser->getEmail());
                    $user->setUsername(explode('@', $googleUser->getEmail())[0]); // Use email prefix as username
                    $user->setName($googleUser->getName() ?? 'Google User');
                    $user->setPhonenumber(''); // No phone from Google, leave empty
                    $user->setStatus('active'); // Default status
                    $user->setRole('USER'); // Default role
                    $user->setPassword(''); // No password set (OAuth users)

                    $this->em->persist($user);
                    $this->em->flush();
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?RedirectResponse
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);

        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('app_home')); // Change to your homepage route name
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('app_login')); // Change to your login route name
    }
}
