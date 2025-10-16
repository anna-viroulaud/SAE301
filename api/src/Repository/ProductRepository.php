<?php

require_once("src/Repository/EntityRepository.php");
require_once("src/Class/Product.php");

/**
 *  Classe ProductRepository
 * 
 *  Cette classe représente le "stock" de Product.
 *  Toutes les opérations sur les Product doivent se faire via cette classe 
 *  qui tient "synchro" la bdd en conséquence.
 * 
 *  La classe hérite de EntityRepository ce qui oblige à définir les méthodes  (find, findAll ... )
 *  Mais il est tout à fait possible d'ajouter des méthodes supplémentaires si
 *  c'est utile !
 *  
 */
class ProductRepository extends EntityRepository {

    public function __construct(){
        // appel au constructeur de la classe mère (va ouvrir la connexion à la bdd)
        parent::__construct();
    }

    /**
     * Récupère un produit par son id
     */
    public function find($id): ?Product {
        $requete = $this->cnx->prepare("SELECT * FROM Product WHERE id = :value");
        $requete->bindParam(':value', $id, PDO::PARAM_INT);
        $requete->execute();
        $answer = $requete->fetch(PDO::FETCH_OBJ);
        
        if ($answer == false) return null;
        
        $p = new Product($answer->id);
        $p->setName($answer->name);
        $p->setIdcategory($answer->category);
        $p->setPrice($answer->price ?? 0);
        $p->setImage($answer->image ?? null);
        return $p;
    }

    /**
     * Récupère tous les produits
     */
    public function findAll(): array {
        $requete = $this->cnx->prepare("SELECT * FROM Product");
        $requete->execute();
        $answer = $requete->fetchAll(PDO::FETCH_OBJ);

        $res = [];
        foreach($answer as $obj){
            $p = new Product($obj->id);
            $p->setName($obj->name);
            $p->setIdcategory($obj->category);
            $p->setPrice($obj->price ?? 0);
            $p->setImage($obj->image ?? null);
            $res[] = $p;
        }
       
        return $res;
    }

    /**
     * Enregistre un nouveau produit en base
     */
    public function save($product): bool {
        $requete = $this->cnx->prepare("
            INSERT INTO Product (name, category, price, image)
            VALUES (:name, :idcategory, :price, :image)
        ");

        $name = $product->getName();
        $idcat = $product->getIdcategory();
        $price = $product->getPrice();
        $image = $product->getImage();

        $requete->bindParam(':name', $name);
        $requete->bindParam(':idcategory', $idcat);
        $requete->bindParam(':price', $price);
        $requete->bindParam(':image', $image);

        $answer = $requete->execute();

        if ($answer){
            $id = $this->cnx->lastInsertId();
            $product->setId($id);
            return true;
        }
          
        return false;
    }

    /**
     * Supprime un produit
     */
    public function delete($id): bool {
        $requete = $this->cnx->prepare("DELETE FROM Product WHERE id = :id");
        $requete->bindParam(':id', $id, PDO::PARAM_INT);
        return $requete->execute();
    }

    /**
     * Met à jour un produit existant
     */
    public function update($product): bool {
        $requete = $this->cnx->prepare("
            UPDATE Product
            SET name = :name,
                category = :idcategory,
                price = :price,
                image = :image
            WHERE id = :id
        ");

        $id = $product->getId();
        $name = $product->getName();
        $idcat = $product->getIdcategory();
        $price = $product->getPrice();
        $image = $product->getImage();

        $requete->bindParam(':id', $id, PDO::PARAM_INT);
        $requete->bindParam(':name', $name);
        $requete->bindParam(':idcategory', $idcat);
        $requete->bindParam(':price', $price);
        $requete->bindParam(':image', $image);

        return $requete->execute();
    }
}
