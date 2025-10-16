<?php

require_once("src/Repository/EntityRepository.php");
require_once("src/Class/Category.php");

/**
 *  Classe CategoryRepository
 * 
 *  Gère toutes les opérations sur les catégories
 */
class CategoryRepository extends EntityRepository {

    public function __construct(){
        parent::__construct();
    }

    public function find($id): ?Category {
        $requete = $this->cnx->prepare("select * from Category where id=:value");
        $requete->bindParam(':value', $id);
        $requete->execute();
        $answer = $requete->fetch(PDO::FETCH_OBJ);
        
        if ($answer == false) return null;
        
        $c = new Category($answer->id);
        $c->setName($answer->name);
        return $c;
    }

    public function findAll(): array {
        $requete = $this->cnx->prepare("select * from Category");
        $requete->execute();
        $answer = $requete->fetchAll(PDO::FETCH_OBJ);

        $res = [];
        foreach($answer as $obj){
            $c = new Category($obj->id);
            $c->setName($obj->name);
            array_push($res, $c);
        }
       
        return $res;
    }

    public function save($category){
        $requete = $this->cnx->prepare("insert into Category (name) values (:name)");
        $name = $category->getName();
        $requete->bindParam(':name', $name);
        $answer = $requete->execute();

        if ($answer){
            $id = $this->cnx->lastInsertId();
            $category->setId($id);
            return true;
        }
          
        return false;
    }

    public function delete($id){
        // Not implemented
        return false;
    }

    public function update($category){
        // Not implemented
        return false;
    }

    /**
     * Get products count by category
     */
    public function getProductsCountByCategory(): array {
        $requete = $this->cnx->prepare("
            SELECT c.id, c.name, COUNT(p.id) as count 
            FROM Category c 
            LEFT JOIN Product p ON c.id = p.category 
            GROUP BY c.id, c.name
        ");
        $requete->execute();
        $answer = $requete->fetchAll(PDO::FETCH_OBJ);

        $res = [];
        foreach($answer as $obj){
            $res[] = [
                "id" => $obj->id,
                "name" => $obj->name,
                "count" => (int)$obj->count
            ];
        }
       
        return $res;
    }
}

?>