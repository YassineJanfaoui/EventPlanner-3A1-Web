<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ForgetPasswordController extends AbstractController
{
    #[Route('/reset-password', name: 'reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(
        Request $request,
        MailerInterface $mailer,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            if ($email) {
                $user = $userRepository->findOneBy(['email' => $email]);

                if ($user) {
                    // Generate a new random password
                    $newPassword = bin2hex(random_bytes(4)); // 8-character password

                    // Hash and update user's password
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                    $user->setPassword($hashedPassword);
                    $em->flush();

                    // Send the new password via email
                    $emailMessage = (new Email())
                        ->from('noreply@yourapp.com')
                        ->to($user->getEmail())
                        ->subject('Your Account Password Reset')
                        ->text(
                            sprintf(
                                "Hello%s,\n\n" .
                                "As requested, we have reset your password. Your new login credentials are as follows:\n\n" .
                                "    Username: %s\n" .
                                "    New Password: %s\n\n" .
                                "You can now log in to your account using the link below:\n" .
                                "    http://127.0.0.1:8000/login\n\n" .
                                "For security reasons, please consider changing your password immediately after logging in.\n\n" .
                                "If you did not request this change, please contact US.\n\n" .
                                "Best regards,\n" .
                                "HackForge\n" .
                                "hackforge3a1@gmail.com",
                                $user->getName() ? ' ' . $user->getName() : '',
                                $user->getUsername(),
                                $newPassword
                            )
                        );


                    $mailer->send($emailMessage);

                    $this->addFlash('success', 'A new password has been sent to your email.');
                } else {
                    $this->addFlash('error', 'No user found with that email.');
                }
            }
        }

        return $this->render('user/forgot_password.html.twig');
    }
}
