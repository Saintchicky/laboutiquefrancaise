<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Adress;
use App\Form\AdressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountAdressController extends AbstractController
{
    // Déclarer Doctrine
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/compte/adresses", name="account_adress")
     */
    public function index(): Response
    {
       // pas besoin rechercher les adresses via user, elles sont liées et stockées ds app
        return $this->render('account/adress.html.twig');
    }
    /**
     * @Route("/compte/ajouter-une-adresse", name="account_adress_to_add")
     */
    public function add(Cart $cart, Request $request): Response
    {
        // pas besoin rechercher les adresses via user, elles sont liées et stockées ds app
        $adresses = new Adress();
        $form = $this->createForm(AdressType::class, $adresses);
        // Ecoute le type de requete du formulaire Get ou Post
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            // relier le user connecté à l'adresse
            $adresses->setUser($this->getUser());
            // Figer la donnée avec doctrine
            $this->entityManager->persist($adresses);
            $this->entityManager->flush();
            // si donnée ds panier et pas d'adresse
            if($cart->get()){
                return $this->redirectToRoute('order');
            }else{
                // a partir du bo du client on crée l'adresse et pas de redirection commande
                return $this->redirectToRoute('account_adress');
            }
        }
        return $this->render('account/adress_form.html.twig',[
            'form'=> $form->createView()
        ]);
    }
    /**
     * @Route("/compte/modifier-une-adresse/{id}", name="account_adress_to_edit")
    */
    public function edit(Request $request, $id): Response
    {
        // pas besoin rechercher les adresses via user, elles sont liées et stockées ds app
        $adresses = $this->entityManager->getRepository(Adress::class)->findOneById($id);
        if(!$adresses || $adresses->getUser() != $this->getUser()){
            return $this->redirectToRoute('account_adress');
        }
        $form = $this->createForm(AdressType::class, $adresses);
        // Ecoute le type de requete du formulaire Get ou Post
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->entityManager->flush();
            return $this->redirectToRoute('account_adress');
        }
        return $this->render('account/adress_form.html.twig',[
            'form'=> $form->createView()
        ]);
    }
    /**
     * @Route("/compte/supprimer-une-adresse/{id}", name="account_adress_to_delete")
    */
    public function delete($id): Response
    {
        // pas besoin rechercher les adresses via user, elles sont liées et stockées ds app
        $adresses = $this->entityManager->getRepository(Adress::class)->findOneById($id);
        if($adresses || $adresses->getUser() == $this->getUser()){
            $this->entityManager->remove($adresses);
            $this->entityManager->flush();
          
        }
        return $this->redirectToRoute('account_adress');
    }
}
