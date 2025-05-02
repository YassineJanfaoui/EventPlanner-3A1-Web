<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserTypeFront;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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
use Twilio\Rest\Client;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;


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
        // Handle file upload
        $imageFile = $form->get('imageFile')->getData();
        if ($imageFile) {
            $uploadsDir = $this->getParameter('kernel.project_dir').'/public/uploads/faceid/faces';
            
            // Ensure directory exists
            if (!file_exists($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }

            // Generate filename from username
            $newFilename = $user->getUsername().'.'.$imageFile->guessExtension();
            
            try {
                $imageFile->move(
                    $uploadsDir,
                    $newFilename
                );
                
                // Create a new File object pointing to the uploaded file
                $uploadedFile = new File($uploadsDir.'/'.$newFilename);
                $user->setImageFile($uploadedFile);
            } catch (FileException $e) {
                $this->addFlash('error', 'There was an error uploading your profile picture.');
                // You might want to return here if the image is required
            }
        }
        $recaptchaResponse = $request->request->get('g-recaptcha-response');
        $recaptchaSecret = $_ENV['RECAPTCHA_SECRET_KEY']; // stored in .env
        $recaptchaVerifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        $verifyResponse = file_get_contents($recaptchaVerifyUrl . '?secret=' . $recaptchaSecret . '&response=' . $recaptchaResponse);
        $responseData = json_decode($verifyResponse);

        if (!$responseData->success) {
            $this->addFlash('error', 'reCAPTCHA verification failed. Please try again.');
            return $this->redirectToRoute('app_user_register');
        }
        
        $user->setStatus('active');
        $user->setImageName($newFilename);
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
        'recaptcha_site_key' => $_ENV['RECAPTCHA_SITE_KEY']
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
public function editProfil(
    Request $request,
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher,
    LoggerInterface $logger
): Response {
    /** @var User $user */
    $user = $this->getUser();
    $originalImage = $user->getImageName(); // Store original image name

    $form = $this->createForm(UserTypeFront::class, $user, [
        'is_edit' => true
    ]);
    $form->remove('role'); // Remove role field for profile editing

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        try {
            // Handle file upload
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $uploadsDir = $this->getParameter('kernel.project_dir').'/public/uploads/faceid/faces';
                
                // Ensure directory exists
                if (!file_exists($uploadsDir)) {
                    mkdir($uploadsDir, 0777, true);
                }

                // Generate filename from username
                $newFilename = $user->getUsername().'.'.$imageFile->guessExtension();
                
                try {
                    // Remove old image if it exists
                    if ($originalImage && file_exists($uploadsDir.'/'.$originalImage)) {
                        unlink($uploadsDir.'/'.$originalImage);
                    }
                    
                    $imageFile->move($uploadsDir, $newFilename);
                    
                    // Update the image name in the entity
                    $user->setImageName($newFilename);
                } catch (FileException $e) {
                    $logger->error('File upload error: '.$e->getMessage());
                    $this->addFlash('error', 'There was an error uploading your profile picture.');
                }
            }

            // Handle password change
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }

            $entityManager->flush();
            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('app_participant_index');

        } catch (\Exception $e) {
            // Revert image if error occurs
            $user->setImageName($originalImage);
            $logger->error('Profile update error: '.$e->getMessage());
            $this->addFlash('error', 'Error updating profile: '.$e->getMessage());
        }
    }

    return $this->render('user/editProfil.html.twig', [
        'form' => $form->createView(),
        'user' => $user
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

#[Route('/{userid}/activate', name: 'app_user_activate', methods: ['GET', 'POST'])]
public function activate(User $user, EntityManagerInterface $entityManager): Response
{
    $user->setStatus('active');
    $entityManager->flush();
    
    $this->sendWhatsAppNotification(
        $user->getPhonenumber(),
        "Your account has been activated. 🎉 Welcome back!"
    );

    $this->addFlash('success', 'User activated successfully.');
    return $this->redirectToRoute('app_user_index');
}

#[Route('/{userid}/deactivate', name: 'app_user_deactivate', methods: ['GET', 'POST'])]
public function deactivate(User $user, EntityManagerInterface $entityManager): Response
{
    $user->setStatus('inactive');
    $entityManager->flush();
    
    $this->sendWhatsAppNotification(
        $user->getPhonenumber(),
        "Your account has been deactivated temporarily. Please contact support for more information."
    );

    $this->addFlash('success', 'User deactivated successfully.');
    return $this->redirectToRoute('app_user_index');
}

#[Route('/{userid}/ban', name: 'app_user_ban', methods: ['GET', 'POST'])]
public function ban(User $user, EntityManagerInterface $entityManager): Response
{
    $user->setStatus('banned');
    $entityManager->flush();
    
    $this->sendWhatsAppNotification(
        $user->getPhonenumber(),
        "Your account has been banned. ❌ If you believe this is a mistake, please contact the administration."
    );

    $this->addFlash('success', 'User banned successfully.');
    return $this->redirectToRoute('app_user_index');
    
}

private function sendWhatsAppNotification(string $phoneNumber, string $message): void
{
    $sid = 'AC8d137b62e199ee41b14b96190617251e';
    $token = 'e58c67bd7bdd3093fe8a8a8c55e71fde';
    $twilioNumber = 'whatsapp:+14155238886';

    $client = new \Twilio\Rest\Client($sid, $token);

    try {
        $client->messages->create(
            'whatsapp:' . $phoneNumber, // No double +
            [
                'from' => $twilioNumber,
                'body' => $message
            ]
        );
    } catch (\Exception $e) {
        error_log('Twilio WhatsApp Error: ' . $e->getMessage());
        // Optionally add flash or logger here
    }
}


}