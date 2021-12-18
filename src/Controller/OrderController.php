<?php

namespace App\Controller;

use DateTime;
use App\Classe\Cart;
use App\Entity\Order;
use App\Form\OrderType;
use App\Entity\OrderDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    // Déclarer Doctrine
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }
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
        return $this->render('order/index.html.twig',[
            'form' => $form->createView(),
            'cart' =>$cart->getFull()
        ]);
    }
    /**
     * @Route("/commande/recapitulatif", name="order_recap", methods={"POST"})
     */
    public function add(Cart $cart, Request $request): Response
    {
        // en deuxieme paramètre on ajouter une entity mais il y en a pas donc on met null
        $form = $this->createForm(OrderType::class, null, [
            // recup que l'utilisateur connecté et dc son id
            'user' =>$this->getUser()
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $date = new DateTime();
            $carriers = $form->get('carriers')->getData();
            $delivery = $form->get('adresses')->getData();
            $delivery_content = $delivery->getFirstName().' '.$delivery->getLastName();
            $delivery_content .= '<br>'.$delivery->getPhone();
            if($delivery->getCompany()){
                $delivery_content .= '<br>'.$delivery->getCompany();
            }

            $delivery_content .= '<br>'.$delivery->getAdress();
            $delivery_content .= '<br>'.$delivery->getPostal().' '.$delivery->getCity();
            $delivery_content .= '<br>'.$delivery->getCountry();

            $order = new Order();
            // générer une référence
            $reference = $date->format('dmY')."-".uniqid();
            $order->setReference($reference);
            $order->setUser($this->getUser());
            $order->setCreatedAt($date);
            $order->setCarrierName($carriers->getName());
            $order->setCarrierPrice($carriers->getPrice());
            $order->setDelivery($delivery_content);
            // la commande est au stade pas payée
            $order->setState(0);
            // prépare les données
            $this->entityManager->persist($order);

            // enregistrer mes produits
            foreach ($cart->getFull() as $product) {
                $orderDetails = new OrderDetails();
                $orderDetails->setMyOrder($order);
                $orderDetails->setProduct($product['product']->getName());
                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['product']->getPrice());
                $orderDetails->setTotal($product['product']->getPrice() * $product['quantity']);
                $this->entityManager->persist($orderDetails);

            // enregistrer mes produits
            }
            // save les données en bdd
            $this->entityManager->flush();
            return $this->render('order/add.html.twig',[
                'cart' =>$cart->getFull(),
                'carrier' => $carriers,
                'delivery' =>$delivery_content,
                'reference' =>$order->getReference()
            ]);
        }
        // redirection si pas de post
        return $this->redirectToRoute('cart');
    }
}
