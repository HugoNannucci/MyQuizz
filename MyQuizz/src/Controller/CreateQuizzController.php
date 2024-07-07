<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Form\Categorie1Type;
use App\Form\Question1Type;
use App\Form\Reponse1Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;

class CreateQuizzController extends AbstractController
{
    #[Route('/user/create/quizz', name: 'create_quizz', methods: ['GET', 'POST'])]
    public function createQuizz(Request $request, EntityManagerInterface $em): Response
    {
        $categorie = new Categorie();

        
        for ($i = 0; $i < 10; $i++) {
            $question = new Question();
            $question->setCategorie($categorie);
            for ($j = 0; $j < 3; $j++) {
                $reponse = new Reponse();
                $reponse->setQuestion($question);
                $question->addReponse($reponse);
            }
            $categorie->addQuestion($question);
            $em->persist($question);
        }
        $form = $this->createForm(Categorie1Type::class, $categorie);
        $form->handleRequest($request);
        // foreach ($questionForms as $questionForm) {
        //     $questionForm->handleRequest($request);
        // }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie);
            $em->flush();

            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('create_quizz/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}