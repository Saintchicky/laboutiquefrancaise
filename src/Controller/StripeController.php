<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Classe\Cart;
use Stripe\Checkout\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    /**
     * @Route("/create-session", name="stripe_create_dossier")
     */
    public function index(Cart $cart): Response
    {
        $product_for_strip = [];
        $YOUR_DOMAIN = "https://127.0.0.1:8000";
        Stripe::setApiKey('sk_test_51Jre7yKdNoa2TDoRfiHikFCO9Ki2pixya0CUMqH207WytJs3pDZapm9zNd2EYElZvA7TDPju9KElBOXRJz7uQI9O00MRmt18Hd');
        foreach ($cart->getFull() as $product) {
            $product_for_strip[] = [
                'price_data' => [
                  'currency' => 'eur',
                  'product_data' => [
                    'name' => $product['product']->getName(),
                    'images'=>[$YOUR_DOMAIN."/uploads/".$product['product']->getIllustration()]
                  ],
                  'unit_amount' => $product['product']->getPrice(),
                ],
                'quantity' => $product['quantity'],
            ];
        }    
        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [$product_for_strip],
            'mode' => 'payment',
            'success_url' => 'https://'.$YOUR_DOMAIN.'/success',
            'cancel_url' => 'https://'.$YOUR_DOMAIN.'/cancel',
        ]);
        // retourne au format json
        $response = new JsonResponse(['id' => $checkout_session->id]);
        return $response;
    }
}
