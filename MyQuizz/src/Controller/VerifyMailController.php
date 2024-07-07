<?php

namespace App\Controller;

use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

class VerifyMailController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/please/verify', name: 'app_please_verify')]
    public function index(): Response
    {
        $user = $this->getUser();
        if ($user === null) return $this->redirectToRoute('app_register');
        $isVerified = $user->isVerified();
        if ($isVerified) {
            return $this->redirectToRoute('app_user_profile');
        }
        return $this->render('registration/first_confirm_email.html.twig');
    }

    #[Route('/resend/email', name: 'app_resend_email')]
    public function resendEmail(): Response
    {
        $user = $this->getUser();
        if ($user === null) return $this->redirectToRoute('app_register');
        $email = $user->getEmail();
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('epitech.test@outlook.com', 'MyQuizz'))
                ->to($email)
                ->subject('Confirmez votre email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
        return $this->redirectToRoute('app_please_verify');
    }
}
