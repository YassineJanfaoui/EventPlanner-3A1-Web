<?php
// src/Controller/SecurityController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use App\Form\LogInForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
public function login(
    Request $request,
    AuthenticationUtils $authenticationUtils,
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher
): Response {
    // If user is already logged in, redirect
    if ($this->getUser()) {
        return $this->redirectToRoute('app_default_target');
    }

    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();

    // Handle form submission
    if ($request->isMethod('POST')) {
        $username = $request->request->get('_username');
        $password = $request->request->get('_password');

        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        
        if (!$user) {
            $this->addFlash('error', 'Invalid credentials');
            return $this->redirectToRoute('app_login');
        }

        // Password Login
        if ($passwordHasher->isPasswordValid($user, $password)) {
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            
            return $this->redirectToRoute('app_default_target');
        } else {
            $this->addFlash('error', 'Invalid credentials');
        }
    }

    // Render the form
    $form = $this->createForm(LogInForm::class, null, [
        'last_username' => $lastUsername,
    ]);

    return $this->render('user/login.html.twig', [
        'loginForm' => $form->createView(),
        'error' => $error,
    ]);
}

#[Route('/faceid', name: 'app_faceid_page')]
    public function faceIdPage(): Response
    {
        return $this->render('user/faceid_login.html.twig');
    }

    #[Route('/login/faceid', name: 'app_login_faceid', methods: ['POST'])]
public function faceIdLogin(Request $request, EntityManagerInterface $em): Response
{
    try {
        // 1. Process the uploaded image
        $imageData = $request->request->get('face_image');
        $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
        $decodedImage = base64_decode($imageData);

        // 2. Save temporary file (debugging)
        $tempDir = $this->getParameter('kernel.project_dir').'/public/uploads/faceid/temp';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $tempFile = $tempDir.'/debug_last_capture.jpg';
        file_put_contents($tempFile, $decodedImage);

        // 3. Run face recognition
        $pythonPath = $this->getParameter('kernel.project_dir').'/faceid.py';
        $facesDir = $this->getParameter('kernel.project_dir').'/public/uploads/faceid/faces';
        
        $command = sprintf(
            '"%s" "%s" "%s" "%s"',
            'C:\Users\ayoub\AppData\Local\Programs\Python\Python313\python.exe',
            $pythonPath,
            $tempFile,
            $facesDir
        );

        $output = shell_exec($command);
        $result = json_decode($output, true);

        // 4. Handle results
        if ($result['match'] === true) {
            $user = $em->getRepository(User::class)->findOneBy([
                'username' => $result['user']  // Matches filename without .jpg
            ]);

            if ($user) {
                // Successful login
                $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                $this->container->get('security.token_storage')->setToken($token);
                
                return $this->json([
                    'success' => true,
                    'redirect' => $this->generateUrl('app_default_target')
                ]);
            }
            return $this->json([
                'success' => false,
                'error' => 'No account exists for recognized face'
            ]);
        }

        return $this->json([
            'success' => false,
            'error' => $result['error'] ?? 'Face not recognized'
        ]);

    } catch (\Exception $e) {
        return $this->json([
            'success' => false,
            'error' => 'Login failed: '.$e->getMessage()
        ]);
    }
}
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/login/redirect', name: 'app_default_target')]
    public function redirectAfterLogin(): Response
    {
        $user = $this->getUser();
        
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->redirectToRoute('app_user_index');
        }
        if (in_array('ROLE_EVENT_PLANNER', $user->getRoles(), true)) {
            return $this->redirectToRoute('app_event_indexx');
        }
        
        return $this->redirectToRoute('app_event_list');
    }

    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectGoogle(ClientRegistry $clientRegistry)
    {
        // Redirects to Google
        return $clientRegistry
            ->getClient('google')
            ->redirect([], []);
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectGoogleCheck(Request $request)
    {
        // This will be handled by Symfony firewall, no code needed here usually
        // You can handle it manually if needed
    }
}