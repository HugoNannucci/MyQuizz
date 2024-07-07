<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;


class AnswerController extends AbstractController
{
    #[Route('/answer', name: 'answer', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // dd($request->query->get('answer'));
        return $this->render('answer.html.twig', [
            'categorie' => $request->query->get('categorie'),
            'question' => $request->query->get('q'),
            'answer' => $request->query->get('answer'),
            'goodanswer' => $request->query->get('goodanswer'),
            'id' => $request->query->get('id'),
            'nb' => $request->query->get('nb'),
            'score' => $request->query->get('score'),
            'max' => $request->query->get('max')
        ]);
    }
}
