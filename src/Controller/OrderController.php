<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Form\OrderType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    /**
     * @Route("/commande", name="order")
     */
    public function index(Cart $cart, Request $request): Response
    {
        // Vérifier si l'utilisateur a des des adresses
        if(!$this->getUser()->getAdresses()->getValues()){
            return $this->redirectToRoute('account_adress_to_add');
        }
        // en deuxieme paramètre on ajouter une entity mais il y en a pas donc on met null
        $form = $this->createForm(OrderType::class, null, [
            // recup que l'utilisateur connecté et dc son id
            'user' =>$this->getUser()
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
        }
        return $this->render('order/index.html.twig',[
            'form' => $form->createView(),
            'cart' =>$cart->getFull()
        ]);
    }
}
