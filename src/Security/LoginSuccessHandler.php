<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return new RedirectResponse($this->router->generate('app_user_index'));
        }

        if (
            in_array('ROLE_EVENT_PLANNER', $user->getRoles(), true) ||
            in_array('ROLE_TEAM_LEADER', $user->getRoles(), true) ||
            in_array('ROLE_SIMPLE_USER', $user->getRoles(), true)
        ) {
            return new RedirectResponse($this->router->generate('app_participant_index'));
        }

        // Default fallback
        return new RedirectResponse($this->router->generate('app_login'));
    }
}
