<?php

namespace App\Controller;

use App\Classe\Search;
use App\Entity\Product;
use App\Form\SearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    // Déclarer Doctrine
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/nos-produits", name="products")
     */
    public function index(Request $request): Response
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();
        $search = new Search();
        $form = $this->createForm(SearchType::class,$search);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            // pas besoin de faire un get Data, le search recupere deja les données car chargé ds form
            // findWithSearch fonction crée ds le repository productRepository
            $products = $this->entityManager->getRepository(Product::class)->findWithSearch($search);
        }else{
            $products = $this->entityManager->getRepository(Product::class)->findAll();
        }    
        return $this->render('product/index.html.twig',[
            'products'=> $products,
            'form'=> $form->createView()
        ]);
    }
        /**
     * @Route("/produit/{slug}", name="product")
     */
    public function show($slug): Response
    {
        // symfony retrouve le slug si dans entity 
        $product = $this->entityManager->getRepository(Product::class)->findOneBySlug($slug);
        $products = $this->entityManager->getRepository(Product::class)->findByIsBest(1);
        if(!$product){
            return $this->redirectRoute('products');
        }
        return $this->render('product/show.html.twig',[
            'product'=> $product,
            'products'=>$products
        ]);
    }
}
