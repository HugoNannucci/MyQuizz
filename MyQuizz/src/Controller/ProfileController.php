<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/user/profile', name: 'app_user_profile')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $isVerified = $user->isVerified();
        if (!$isVerified) return $this->redirectToRoute('app_please_verify');
//        $user->setRoles(array('ROLE_ADMIN'));
//        $entityManager->flush();
        $user->setLastLogged(date('Y-m-d', time()));
        $entityManager->flush();
        $roles = $user->getRoles();
        return $this->render('profile/profil.html.twig', [
            'user' => $user,
            'roles' => $roles
        ]);
    }
}
