<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
    public function index(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $notification = null;
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        // Ecoute le type de requete du formulaire Get ou Post
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            // recupère les données du formulaire
            $user = $form->getData();
            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());
            if(!$search_email){
                //Encoder le mot de passe on recupere la donnée par le getter
                $password = $encoder->encodePassword($user,$user->getPassword());
                // Une fois la donnée changée, on set celle ci avant envoie ds la bdd
                $user->setPassword($password);
                // Figer la donnée avec doctrine
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $mail = new Mail();
                $content = "Bonjour ".$user->getFirstname()."<br>Bienvenue sur la première boutique dédiée au made in France.<br><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent non rutrum massa. Aenean dapibus venenatis elementum. Vivamus auctor at dolor vel pulvinar. Fusce vitae porttitor turpis. Nulla sed erat malesuada, tempor ipsum eget, tempus nisi. Nulla sed lorem sagittis, ullamcorper metus vitae, tempus augue. Nunc viverra felis leo, a varius nunc molestie eu. Pellentesque cursus, augue ut rhoncus venenatis, neque massa aliquet nunc, a porta purus est pulvinar lorem. Morbi at feugiat mauris. Duis porttitor ante non ornare maximus. Nam fringilla ligula quam, id laoreet velit elementum sed. Morbi aliquet hendrerit diam, ut semper justo. Praesent sit amet dui ex. Mauris massa velit, placerat vel magna vitae, eleifend rutrum arcu.";
                $mail->send($user->getEmail(), $user->getFirstname(), "Bienvenue sur la Boutique Française", $content);
                $notification = "Votre inscription s'est bien déroulée. Vous pouvez dès à présent vous connecter à votre compte";
            }else{
                $notification = "L'email que vous renseigné existe déjà.";
            }
        }
        return $this->render('register/index.html.twig',[
            'form'=>$form->createView(),
            'notification'=>$notification
        ]);
    }
}
