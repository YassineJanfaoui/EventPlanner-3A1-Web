<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Psr\Log\LoggerInterface; // Add this for LoggerInterface
use Symfony\Component\Security\Core\Exception\AuthenticationException; // Add this for AuthenticationException

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
public function login(AuthenticationUtils $authenticationUtils, LoggerInterface $logger): Response
{
    // If user is already logged in
    if ($this->getUser()) {
        return $this->redirectBasedOnRole($this->getUser());
    }

    // Get login error if there is one
    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();

    if ($error) {
        $logger->error('Authentication failed', [
            'username' => $lastUsername,
            'error' => $error->getMessageKey(),
            'exception' => $error->getMessage()
        ]);
        
        $this->addFlash('error', $this->getLoginErrorMessage($error));
    }

    return $this->render('user/login.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error
    ]);
}

private function getLoginErrorMessage(AuthenticationException $error): string
{
    $errorKey = $error->getMessageKey();
    $data = $error->getMessageData();

    switch ($errorKey) {
        case 'Invalid credentials.':
            return 'Invalid username or password.';
        case 'User account is disabled.':
            return 'Your account is disabled.';
        case 'User account is locked.':
            return 'Your account is locked.';
        default:
            return 'Login failed. Please try again.';
    }
}

private function redirectBasedOnRole(User $user): Response
{
    if (in_array('ROLE_ADMIN', $user->getRoles())) {
        return $this->redirectToRoute('app_user_show', ['userid' => $user->getUserid()]);
    }
    return $this->redirectToRoute('app_participant_index');
}


    
        #[Route('/logout', name: 'app_logout', methods: ['GET'])]
        public function logout(): void
        {
            throw new \LogicException('This will be intercepted by the firewall');
        }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{userid}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{userid}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{userid}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getUserid(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}