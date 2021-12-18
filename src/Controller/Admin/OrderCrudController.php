<?php

namespace App\Controller\Admin;

use App\Entity\Order;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class OrderCrudController extends AbstractCrudController
{
    private $entityManager;
    private $crudUrlGenerator;
    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator){
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }
    // customiser le actions
    public function configureActions(Actions $actions): Actions
    {
        $updatePreparation = Action::new('updatePreparation', 'Préparation en cours','fas fa-box-open')->linkToCrudAction('updatePreparation');
        $updateDelivery = Action::new('updateDelivery', 'Livraison en cours','fas fa-truck')->linkToCrudAction('updateDelivery');
        return $actions
                // premier paramètre la page et deuxieme l'action
                ->add('detail', $updatePreparation)
                ->add('detail', $updateDelivery)
                ->add('index', 'detail');
    }
    // attention CrudUrlGenerator est déprécié
    // admin context permet de recupérer directement l'objet order
    public function updatePreparation(AdminContext $context){
        $order = $context->getEntity()->getInstance();
        $order->setState(2);
        // sauvegarde statut préparation en cours -> 2
        $this->entityManager->flush();
        // ajouter un message pr informer le user
        $this->addFlash('notice',"<span style='color:green;'><strong>La commande ".$order->getReference()." est <u>bien en cours de préparation</u>.</strong></span>");
        
        $url = $this->adminUrlGenerator
                // mettre sur le controller crud Order
                ->setController(OrderCrudController::class)
                // revenir sur la page des orders
                ->setAction('index')
                ->generateUrl();
        return $this->redirect($url);
    }
    public function updateDelivery(AdminContext $context){
        $order = $context->getEntity()->getInstance();
        $order->setState(3);
        // sauvegarde statut préparation en cours -> 2
        $this->entityManager->flush();
        // ajouter un message pr informer le user
        $this->addFlash('notice',"<span style='color:orange;'><strong>La commande ".$order->getReference()." est <u>bien en cours de livraison</u>.</strong></span>");
        
        $url = $this->adminUrlGenerator
                // mettre sur le controller crud Order
                ->setController(OrderCrudController::class)
                // revenir sur la page des orders
                ->setAction('index')
                ->generateUrl();
        return $this->redirect($url);
    }
    public function configureCrud(Crud $crud): Crud
    {
        // reclasse en décroissant
        return $crud->setDefaultSort(['id'=>'DESC']);
    }
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            DateTimeField::new('createdAt', 'Passée le'),
            TextField::new('user.getFullName', 'Utilisateur'),
            TextEditorField::new('delivery', 'Adresse de livraison')->onlyOnDetail(),
            MoneyField::new('total','Total produit')->setCurrency('EUR'),
            TextField::new('carrierName', 'Transporteur'),
            MoneyField::new('carrierPrice','Frais de port')->setCurrency('EUR'),
            ChoiceField::new('state')->setChoices([
                'Non payée' => 0,
                'Payée' => 1,
                'Préparation en cours' => 2,
                'Livraison en cours' => 3,
            ]),
            // pr eviter de faire apparaitre une colonne en trop hideOnIndex
            ArrayField::new('orderDetails', 'Produits achetés')->hideOnIndex()
        ];
    }

}
