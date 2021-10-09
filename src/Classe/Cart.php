<?php

namespace App\Classe;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart{
    private $session;
    // Déclarer Doctrine
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager,SessionInterface $session){
        $this->session = $session;
        $this->entityManager = $entityManager;
    }
    public function add($id){
        $cart = $this->session->get('cart', []);
        // si le panier n'est pas vide
        if(!empty($cart[$id])){
            // on incrémente 
            $cart[$id] ++;
        }else{
            $cart[$id] = 1;
        }
        $this->session->set('cart',$cart);
    }
    public function get(){
        return $this->session->get('cart');
    }
    public function remove(){
        return $this->session->remove('cart');
    }
    public function delete($id){
        $cart = $this->session->get('cart', []);
        unset($cart[$id]);
        return $this->session->set('cart',$cart);
    }
    public function decrease($id){
        $cart = $this->session->get('cart', []);
        // si quantité supp a 1 on décremente
        if($cart[$id] > 1){
            $cart[$id] --;
        }else{
            // si egal à 1 alors on supprime
            unset($cart[$id]);
        }
        return $this->session->set('cart',$cart);
    }
    public function getFull(){
        $cartComplete = [];
        // si le panier n'est pas vide
        if($this->get()){
            foreach($this->get() as $id =>$quantity){
                $product_object = $this->entityManager->getRepository(Product::class)->findOneById($id);
                // si aucun produit trouvé on supprime
                if(!$product_object){
                    $this->delete($id);
                    // et on sort de la boucle
                    continue;
                }
                $cartComplete[] = [
                    'product' => $product_object,
                    'quantity' =>$quantity,
                ];
            }
            return $cartComplete;
        }
    }
}