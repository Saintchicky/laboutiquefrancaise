<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderSuccessController extends AbstractController
{
    // Déclarer Doctrine
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_validate")
     */
    public function index(Cart $cart,$stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);
        if(!$order || $order->getUser() != $this->getUser()){
            return $this->redirectToRoute('home');
        }
        // si isPais est à 0
        if(!$order->getIsPaid()){
            // vider le panier
            $cart->remove();
            $order->setIsPaid(1);
            $this->entityManager->flush();
        }
        return $this->render('order_success/index.html.twig',[
            'order'=>$order
        ]);
    }
}
