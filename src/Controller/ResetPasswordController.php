<?php

namespace App\Controller;


use App\Classe\Mail;
use App\Entity\User;
use App\Entity\ResetPassword;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordController extends AbstractController
{
    // Déclarer Doctrine
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/mot-de-passe-oublie", name="reset_password")
     */
    public function index(Request $request): Response
    {
        if($this->getUser()){
            return $this->redirectToRoute("home");
        }
        if($request->get('email')){
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));
            if($user){
                // 1 enregistrer en base la demande de reset
                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new \DateTimeImmutable ());
                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();

                $url = $this->generateUrl('update_password', [
                    'token'=>$reset_password->getToken()
                ]);
                // 2 envoyer à l'utilisateur un email ac un lien pr réinit le mot de passe
                $mail = new Mail();
                $content = "Bonjour ".$user->getFirstname()."<br>Vous avez demandé à Réinitialiser votre mot de passe<br><br>";
                $content .= "Merci de cliquer sur le lien suivant pour <a href='".$url."'>mettre à jour votre mot de passe</a>";
                $mail->send($user->getEmail(), $user->getFirstname(), "Réinitialiser votre mot de passe sur La Boutique Française", $content);
                $this->addFlash('notice','Vous allez recevoir un email pour réinitialiser votre mot de passe');
            }else{
                $this->addFlash('notice','Cette adresse email est inconnue');
            }
        }
        return $this->render('reset_password/index.html.twig');
    }
    /**
     * @Route("/modifier-mon-mot-de-passe/{token}", name="update_password")
     */
    public function udapte(Request $request, $token, UserPasswordEncoderInterface $encoder): Response
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);
        if(!$reset_password){
            return $this->redirectToRoute("reset_password");
        }
        // Vérifier si le createdAt = now -3H
        $now = new \DateTime ();
        if($now > $reset_password->getCreatedAt()->modify('+ 3 hour')){
            $this->addFlash('notice','Votre demande de mot de passe a expiré. Merci de le renouveller');
            return $this->redirectToRoute("reset_password");
        }
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $new_pwd = $form->get('new_password')->getData();
            //Encoder le mot de passe on recupere la donnée par le getter
            $password = $encoder->encodePassword($reset_password->getUser(), $new_pwd );
            // Une fois la donnée changée, on set celle ci avant envoie ds la bdd
            $reset_password->getUser()->setPassword($password);
            $this->entityManager->flush();
            $this->addFlash('notice','Votre de mot de passe a bien été mis à jour');
            return $this->redirectToRoute("app_login");
        }
        return $this->render('reset_password/update.html.twig',[
            'form'=>$form->createView()
        ]);
    }
}
