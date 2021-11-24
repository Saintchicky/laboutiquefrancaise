<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\Product;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    /**
     * @Route("/create-session/{reference}", name="stripe_create_dossier")
     */
    public function index(EntityManagerInterface $entityManager, Cart $cart, $reference): Response
    {
        $product_for_strip = [];
        $YOUR_DOMAIN = "https://127.0.0.1:8000";
        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);
        if(!$order){
            return new JsonResponse(['error' => 'order']);
        }
        foreach ($order->getOrderDetails()->getValues() as $product) {
            $product_object = $entityManager->getRepository(Product::class)->findOneByName($product->getProduct());
            $product_for_strip[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images'=>[$YOUR_DOMAIN."/uploads/".$product_object->getIllustration()]
                    ],
                    'unit_amount' => $product->getPrice(),
                ],
                'quantity' => $product->getQuantity(),
            ];
        }
        $product_for_strip[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images'=>[$YOUR_DOMAIN]
                ],
                // plus besoin de faire 100 car on a config ds easyAdmin avec MoneyField le champ price
                'unit_amount' =>  $order->getCarrierPrice(),
            ],
            'quantity' => 1,
        ];    
        Stripe::setApiKey('sk_test_51Jre7yKdNoa2TDoRfiHikFCO9Ki2pixya0CUMqH207WytJs3pDZapm9zNd2EYElZvA7TDPju9KElBOXRJz7uQI9O00MRmt18Hd');
        $checkout_session = Session::create([
            'customer_email'=>$this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [$product_for_strip],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN.'/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN.'/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);
        // enregistrer le sessionId stripe car le client a payé
        $order->setStripeSessionId($checkout_session->id);
        // pas besoin d'utiliser persist et de figer la donnée car l'objet est déjà crée
        $entityManager->flush();
        // retourne au format json
        $response = new JsonResponse(['id' => $checkout_session->id]);
        return $response;
    }
}
