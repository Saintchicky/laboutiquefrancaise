<?php

namespace App\Repository;

use App\Classe\Search;
use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }
    /**
     * Undocumented function
     *
     * requete qui permet de recuperer les categories en fonction de la recherche de l'utilsateur
     * @return Product[]
     */
    public function findWithSearch(Search $search){
        // preparer la requete
        $query = $this
                    // p = product, c'est alias comme ds les requetes sql
                    ->createQueryBuilder('p')
                    ->select('c','p')
                    ->join('p.category','c');
        if(!empty($search->categories)){
            $query = $query 
                        ->andWhere('c.id IN (:categories)')
                        // parametrer :categorie avec la liste
                        ->setParameter('categories', $search->categories);
        }
        if(!empty($search->string)){
            $query = $query 
                        ->andWhere('p.name LIKE :string')
                        // parametrer :categorie avec la liste
                        ->setParameter('string', "%{$search->string}%");
        }
        return $query->getQuery()->getResult();
    }
    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
