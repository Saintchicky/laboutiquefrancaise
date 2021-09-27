<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountPasswordController extends AbstractController
{
    /**
     * @Route("/compte/modifier-mon-mot-passe", name="account_password")
     */
    public function index(): Response
    {
        // Recupère l'user connecté
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);
        return $this->render('account/password.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
