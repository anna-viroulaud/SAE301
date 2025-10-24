<?php

require_once("src/Repository/EntityRepository.php");
require_once("src/Class/Category.php");

/**
 * CategoryRepository - accès à la table Category
 */
class CategoryRepository extends EntityRepository {

    public function __construct(){
        parent::__construct();
    }

    /**
     * Trouver une catégorie par son ID
     */
    public function find($id): ?Category {
        $req = $this->cnx->prepare("SELECT id, name FROM Category WHERE id = :id LIMIT 1");
        $req->bindParam(':id', $id, PDO::PARAM_INT);
        $req->execute();
        $row = $req->fetch(PDO::FETCH_OBJ);
        
        if ($row === false) return null;
        
        $category = new Category();
        $category->setId((int)$row->id);
        $category->setName($row->name);
        $category->setSlug($row->name); // Utilise le name comme slug
        
        // Compter les produits de cette catégorie
        $countReq = $this->cnx->prepare("SELECT COUNT(*) as count FROM Product WHERE category = :catId");
        $countReq->bindParam(':catId', $row->id, PDO::PARAM_INT);
        $countReq->execute();
        $countRow = $countReq->fetch(PDO::FETCH_OBJ);
        $category->setProductCount($countRow ? (int)$countRow->count : 0);
        
        return $category;
    }

    /**
     * Trouver une catégorie par son slug (utilise le name)
     */
    public function findBySlug(string $slug): ?Category {
        $req = $this->cnx->prepare("SELECT id FROM Category WHERE name = :name LIMIT 1");
        $req->bindParam(':name', $slug);
        $req->execute();
        $row = $req->fetch(PDO::FETCH_OBJ);
        
        if ($row === false) return null;
        
        return $this->find((int)$row->id);
    }

    /**
     * Trouver toutes les catégories
     */
    public function findAll(): array {
        $req = $this->cnx->prepare("SELECT id FROM Category ORDER BY name ASC");
        $req->execute();
        $rows = $req->fetchAll(PDO::FETCH_OBJ);
        
        $result = [];
        foreach ($rows as $row) {
            $cat = $this->find((int)$row->id);
            if ($cat !== null) $result[] = $cat;
        }
        return $result;
    }

    /**
     * Sauvegarder une catégorie
     */
    public function save($category): bool {
        if ($category->getId() == 0) {
            // INSERT
            $req = $this->cnx->prepare("INSERT INTO Category (name) VALUES (:name)");
            $name = $category->getName();
            $req->bindParam(':name', $name);
            return $req->execute();
        } else {
            // UPDATE
            $req = $this->cnx->prepare("UPDATE Category SET name = :name WHERE id = :id");
            $id = $category->getId();
            $name = $category->getName();
            $req->bindParam(':id', $id, PDO::PARAM_INT);
            $req->bindParam(':name', $name);
            return $req->execute();
        }
    }

    /**
     * Supprimer une catégorie
     */
    public function delete($id): bool {
        $req = $this->cnx->prepare("DELETE FROM Category WHERE id = :id");
        $req->bindParam(':id', $id, PDO::PARAM_INT);
        return $req->execute();
    }

    /**
     * Mettre à jour une catégorie
     */
    public function update($category): bool {
        return $this->save($category);
    }
}

?>
