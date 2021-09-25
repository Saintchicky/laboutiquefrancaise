<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegisterController extends AbstractController
{
    // Déclarer Doctrine
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/inscription", name="register")
     */
    public function index(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        // Ecoute le type de requete du formulaire Get ou Post
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            // recupère les données du formulaire
            $user = $form->getData();
            // Figer la donnée avec doctrine
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        return $this->render('register/index.html.twig',[
            'form'=>$form->createView()
        ]);
    }
}
