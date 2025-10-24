<?php

require_once "src/Controller/EntityController.php";
require_once "src/Repository/CategoryRepository.php";

/**
 * CategoryController - Gère les requêtes HTTP pour les catégories
 */
class CategoryController extends EntityController {

    private CategoryRepository $categories;

    public function __construct(){
        $this->categories = new CategoryRepository();
    }

    /**
     * Traiter les requêtes GET
     * GET /categories → Liste toutes les catégories
     * GET /categories/:id → Détails d'une catégorie (ID ou slug)
     */
    protected function processGetRequest(HttpRequest $request) {
        $id = $request->getId("id");
        
        if ($id){
            // URI est .../categories/{id} ou .../categories/{slug}
            // Vérifier si c'est un nombre (ID) ou une chaîne (slug)
            if (is_numeric($id)) {
                $category = $this->categories->find((int)$id);
            } else {
                $category = $this->categories->findBySlug($id);
            }
            return $category === null ? false : $category;
        } else {
            // URI est .../categories
            return $this->categories->findAll();
        }
    }

    /**
     * Traiter les requêtes POST (création de catégorie)
     */
    protected function processPostRequest(HttpRequest $request) {
        $json = $request->getJson();
        $obj = json_decode($json);
        
        if (!isset($obj->name)) {
            http_response_code(400);
            return ["error" => "Name required"];
        }
        
        $category = new Category(0);
        $category->setName($obj->name);
        
        $ok = $this->categories->save($category);
        return $ok ? $category : false;
    }
}

?>
