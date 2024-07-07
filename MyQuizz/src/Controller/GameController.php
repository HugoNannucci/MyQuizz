<?php


namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use App\Entity\Question;
use App\Repository\QuestionRepository;
use App\Entity\Reponse;
use App\Repository\ReponseRepository;
use App\Form\AnswerType;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class GameController extends AbstractController
{
    private $post;

    public function __construct()
    {
        $this->post = $_POST;
    }
    #[Route('/', name: 'games', methods: ['GET'])]
    public function getCategories(EntityManagerInterface $entityManager)
    {
        $catrepository = $entityManager->getRepository(Categorie::class);
        $category = $catrepository->findAll();
        $categories = [];
        foreach ($category as $c) {
            array_push($categories, $c->getName());
        }
        return $this->render('games.html.twig', [
            'categories' => $categories,
        ]);
    }
    
    #[Route('/scores', name: 'scores', methods: ['GET'])]
    public function getScores(EntityManagerInterface $entityManager)
    {
        $catrepository = $entityManager->getRepository(Categorie::class);
        $questionrepository = $entityManager->getRepository(Question::class);
        $category = $catrepository->findAll();
        $categories = [];
        $scores = [];
        $max = [];
        foreach ($category as $c) {
            $q = $questionrepository->findBy(['categorie' => $c->getId()]);
            array_push($max, count($q));
            array_push($categories, $c->getName());
            if(isset($_COOKIE["score".trim(str_replace([' ', '-', ':', ','], '',$c->getName()))])){
                array_push($scores, $_COOKIE["score".trim(str_replace([' ', '-', ':', ','], '',$c->getName()))]);
            } else {
                array_push($scores, 'N/A');
            }
        }
        return $this->render('scores.html.twig', [
            'categories' => $categories,
            'scores' => $scores,
            'max' => $max
        ]);
    }

    #[Route('/game/{id}/{q?}', name: 'game', methods: ['POST', 'GET'])]

    public function getQuizz(Request $request, EntityManagerInterface $entityManager, Reponse $rep)
    {
        $catrepository = $entityManager->getRepository(Categorie::class);
        $questionrepository = $entityManager->getRepository(Question::class);
        $reponserepository = $entityManager->getRepository(Reponse::class);
        $params = $request->attributes->get('_route_params');
        $answers = [];
        $form = '';
        $goodanswer = '';
        if ($params['q'] == 1) {
            setcookie('score', 0);
        }
        $category = $catrepository->findBy(['id' => $params['id']]);
        $categorie = $category[0]->getName();
        if (isset($params['q'])) {
            $found = false;
            $q = $questionrepository->findBy(['categorie' => $params['id']]);
            $maxquestions = count($q);
            foreach ($q as $k => $qq) {
                if ($k === ($params['q'] - 1)) {
                    $question = $qq->getQuestion();
                    $questionid = $qq->getId();
                    $a = $reponserepository->findBy(['question' => $questionid]);
                    $g = $reponserepository->findOneBy(['question' => $questionid, 'reponse_expected' => 1]);
                    foreach ($a as $aa) {
                        array_push($answers, $aa->getReponse());
                    }
                    shuffle($answers);
                    $goodanswer = $g->getReponse();

                    $form = $this->createFormBuilder()
                        ->add(
                            'answer',
                            ChoiceType::class,
                            [
                                'choices' => [
                                    $answers[0] => $answers[0],
                                    $answers[1] => $answers[1],
                                    $answers[2] => $answers[2]
                                ],
                                'expanded' => true,
                                'label' => false
                            ]
                        )
                        ->add('save', SubmitType::class, ['label' => 'Valider'])
                        ->getForm();
                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {
                        if ($this->post['form']['answer'] == $goodanswer) {
                            $score = $_COOKIE['score'];
                            setcookie("score", "", time() - 3600);
                            setcookie('score', ($score + 1));
                            return $this->redirectToRoute('answer', ['answer' => $this->post['form']['answer'], 'goodanswer' => $goodanswer, 'q' => $question, 'id' => $params['id'], 'nb' => $params['q'], 'categorie' => $categorie, 'score' => ($_COOKIE['score'] + 1), 'max' => $maxquestions]);
                        } else {
                            return $this->redirectToRoute('answer', ['answer' => $this->post['form']['answer'], 'goodanswer' => $goodanswer, 'q' => $question, 'id' => $params['id'], 'nb' => $params['q'], 'categorie' => $categorie, 'score' => $_COOKIE['score'], 'max' => $maxquestions]);
                        }
                    }

                    $found = true;
                }
            }
            if ($found === false) {
                setcookie("score".trim(str_replace([' ', '-', ':', ','], '',$categorie)), "", time() - 3600);
                setcookie("score".trim(str_replace([' ', '-', ':', ','], '',$categorie)), $_COOKIE['score'],false, '/');
                return $this->render('score.html.twig', [
                    'categorie' => $categorie,
                    'score' => $_COOKIE['score'],
                    'max' => $maxquestions
                ]);
            }
        } else {
            $question = '';
        }

        if ($request == 'POST') {
        }

        return $this->render('game.html.twig', [
            'nb' => $params['q'],
            'id' => $params['id'],
            'categorie' => $categorie,
            'question' => $question,
            'form' => $form,
            'answers' => $answers,
            'goodanswer' => $goodanswer
        ]);
    }
}
