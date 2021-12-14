<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
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
            $mail = new Mail();
            $content = "Bonjour ".$order->getUser()->getFirstname()."<br>Merci pour votre commande.<br><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent non rutrum massa. Aenean dapibus venenatis elementum. Vivamus auctor at dolor vel pulvinar. Fusce vitae porttitor turpis. Nulla sed erat malesuada, tempor ipsum eget, tempus nisi. Nulla sed lorem sagittis, ullamcorper metus vitae, tempus augue. Nunc viverra felis leo, a varius nunc molestie eu. Pellentesque cursus, augue ut rhoncus venenatis, neque massa aliquet nunc, a porta purus est pulvinar lorem. Morbi at feugiat mauris. Duis porttitor ante non ornare maximus. Nam fringilla ligula quam, id laoreet velit elementum sed. Morbi aliquet hendrerit diam, ut semper justo. Praesent sit amet dui ex. Mauris massa velit, placerat vel magna vitae, eleifend rutrum arcu.";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), "Votre commande la boutique française est bien validée", $content);
        }
        return $this->render('order_success/index.html.twig',[
            'order'=>$order
        ]);
    }
}
