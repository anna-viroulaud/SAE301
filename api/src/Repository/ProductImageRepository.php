<?php

require_once("src/Repository/EntityRepository.php");
require_once("src/Class/ProductImage.php"); 

/**
 * Classe ProductImageRepository
 * * Cette classe gère toutes les opérations CRUD (Créer, Lire, Mettre à jour, Supprimer)
 * pour l'entité ProductImage dans la base de données.
 */
class ProductImageRepository extends EntityRepository {

    public function __construct(){
        // Appelle le constructeur de la classe mère (ouvre la connexion à la BDD)
        parent::__construct();
    }

    /**
     * Trouve une ProductImage par son ID.
     * @param int $id L'ID de l'image.
     * @return ProductImage|null L'objet ProductImage ou null si non trouvé.
     */
    public function find($id): ?ProductImage {
        $requete = $this->cnx->prepare("SELECT * FROM ProductImage WHERE id=:value");
        $requete->bindParam(':value', $id, PDO::PARAM_INT);
        $requete->execute();
        $answer = $requete->fetch(PDO::FETCH_OBJ);
        
        if ($answer === false) return null;
        
        $image = new ProductImage($answer->id);
        $image->setProductId($answer->product_id);
        $image->setUrl($answer->url);

        return $image;
    }

    /**
     * Trouve toutes les ProductImage dans la BDD.
     * @return array<ProductImage> Un tableau d'objets ProductImage.
     */
    public function findAll(): array {
        $requete = $this->cnx->prepare("SELECT * FROM ProductImage ORDER BY product_id, id");
        $requete->execute();
        $answer = $requete->fetchAll(PDO::FETCH_OBJ);

        $res = [];
        foreach($answer as $obj){
            $image = new ProductImage($obj->id);
            $image->setProductId($obj->product_id);
            $image->setUrl($obj->url);
            array_push($res, $image);
        }
        
        return $res;
    }

    /**
     * Trouve toutes les images associées à un produit spécifique.
     * C'est la méthode principale utilisée par le ProductImageController.
     * @param int $productId L'ID du produit.
     * @return array<ProductImage> Un tableau d'objets ProductImage pour ce produit.
     */
    public function findAllByProduct(int $productId): array {
        // Sélectionne toutes les images du produit, triées par ID pour un ordre stable.
        $requete = $this->cnx->prepare("SELECT * FROM ProductImage WHERE product_id=:productId ORDER BY id ASC");
        $requete->bindParam(':productId', $productId, PDO::PARAM_INT);
        $requete->execute();
        $answer = $requete->fetchAll(PDO::FETCH_OBJ);

        $res = [];
        foreach($answer as $obj){
            $image = new ProductImage($obj->id);
            $image->setProductId($obj->product_id);
            $image->setUrl($obj->url);
            array_push($res, $image);
        }
        
        return $res;
    }

    /**
     * Enregistre (INSERT) une nouvelle ProductImage dans la BDD.
     * @param ProductImage $image L'objet ProductImage à sauvegarder.
     * @return bool True en cas de succès, false sinon.
     */
    public function save($image): bool {
        // On suppose que l'ID est 0 (nouvel objet)
        $requete = $this->cnx->prepare("INSERT INTO ProductImage (product_id, url) VALUES (:product_id, :url)");
        
        $productId = $image->getProductId();
        $url = $image->getUrl();

        $requete->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $requete->bindParam(':url', $url, PDO::PARAM_STR);

        $answer = $requete->execute();

        if ($answer){
            $id = $this->cnx->lastInsertId();
            $image->setId($id); // Met à jour l'ID réel dans l'objet
            return true;
        }
            
        return false;
    }

    /**
     * Supprime une ProductImage par son ID.
     * @param int $id L'ID de l'image à supprimer.
     * @return bool True si la suppression a réussi, false sinon.
     */
    public function delete($id): bool {
        $requete = $this->cnx->prepare("DELETE FROM ProductImage WHERE id=:id");
        $requete->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $requete->execute();
    }
    
    /**
     * Mettre à jour n'est généralement pas nécessaire pour les images (on utilise save/delete),
     * mais elle est incluse pour respecter le contrat d'EntityRepository si nécessaire.
     */
    public function update($image){
        // Implémentation facultative (TODO when needed)
        return false;
    }
}