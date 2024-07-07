<?php

namespace App\Controller;

use App\Form\EditEmailUserType;
use App\Form\EditPasswordUserType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class EditProfileController extends AbstractController
{
    #[Route('/user/edit/email', name: 'app_edit_email', methods: ['GET', 'POST'])]
    public function editEmail(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditEmailUserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user->setVerified(false);
            $user->setEmail($email);
            $entityManager->flush();
            return $this->redirectToRoute('app_resend_email');
        }
        return $this->render('/profile/edit_email.html.twig', [
            'user' => $user,
            'form' => $form
        ]);
    }

    #[Route('/user/edit/password', name: 'app_edit_password', methods: ['GET', 'POST'])]
    public function editPassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher) : Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditPasswordUserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $entityManager->flush();
            return $this->redirectToRoute('app_logout');
        }
        return $this->render('/profile/edit_password.html.twig', [
            'user' => $user,
            'form' => $form
        ]);
    }
}
