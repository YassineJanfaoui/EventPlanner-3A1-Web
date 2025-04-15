<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserTypeFront;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Psr\Log\LoggerInterface; // Add this for LoggerInterface
use Symfony\Component\Security\Core\Exception\AuthenticationException; // Add this for AuthenticationException
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Add this import


#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => false
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Verify plainPassword exists before hashing
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }
            
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User created successfully');
            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/register', name: 'app_user_register', methods: ['GET', 'POST'])]
public function register(
    Request $request, 
    EntityManagerInterface $entityManager, 
    UserPasswordHasherInterface $passwordHasher
): Response {
    $user = new User();
    $form = $this->createForm(UserTypeFront::class, $user, [
        'is_edit' => false
    ]);
    
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Set default status to 'active'
        $user->setStatus('active');
        
        // Hash the plain password
        $plainPassword = $form->get('plainPassword')->getData();
        if ($plainPassword) {
            $user->setPassword(
                $passwordHasher->hashPassword($user, $plainPassword)
            );
        }
        
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Registration successful! You can now login.');
        return $this->redirectToRoute('app_login');
    }

    return $this->render('user/Register.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/profile/edit', name: 'app_user_edit_profil', methods: ['GET', 'POST'])]
public function EditProfil(
    Request $request, 
    EntityManagerInterface $entityManager, 
    UserPasswordHasherInterface $passwordHasher
    ): Response 
    {
    /** @var User $user */
    $user = $this->getUser();

    // Create the form using the front-facing form type
    $form = $this->createForm(UserTypeFront::class, $user, [
        'is_edit' => true // To control optional password and constraints
    ]);

    // Remove role field to prevent changes to it
    $form->remove('role');

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Only update password if the user typed a new one
        $newPassword = $form->get('plainPassword')->getData();
        if ($newPassword) {
            $user->setPassword(
                $passwordHasher->hashPassword($user, $newPassword)
            );
        }

        $entityManager->flush();

        // You can customize redirection as needed
        return $this->redirectToRoute('app_participant_index'); // or any profile/dashboard route
    }

    return $this->render('user/editProfil.html.twig', [
        'form' => $form->createView(),
    ]);
    }


    #[Route('/{userid}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
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

    // In your UserController
    

#[Route('/{userid}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
public function edit(
    Request $request, 
    User $user, 
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher
): Response {
    $form = $this->createForm(UserType::class, $user, [
        'is_edit' => true // Explicitly set for edit
    ]);
    
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Only hash password if it was provided
        if ($form->get('plainPassword')->getData()) {
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
        }
        
        $entityManager->flush();

        return $this->redirectToRoute('app_user_index');
    }

    return $this->render('user/edit.html.twig', [
        'user' => $user,
        'form' => $form->createView(),
    ]);
}
}