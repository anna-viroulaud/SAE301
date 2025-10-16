<?php
require_once "src/Controller/EntityController.php";
require_once "src/Repository/CategoryRepository.php";

/**
 * CategoryController
 * Gère les requêtes relatives aux catégories
 */
class CategoryController extends EntityController {

    private CategoryRepository $categories;

    public function __construct(){
        $this->categories = new CategoryRepository();
    }

   
    protected function processGetRequest(HttpRequest $request) {
        $id = $request->getId("id");
        if ($id){
            // URI is .../categories/{id}
            $c = $this->categories->find($id);
            return $c == null ? false : $c;
        }
        else{
            // URI is .../categories
            // Check if we need products count
            $withCount = $request->getParam("count");
            if ($withCount !== false && $withCount === "true") {
                return $this->categories->getProductsCountByCategory();
            }
            return $this->categories->findAll();
        }
    }

    protected function processPostRequest(HttpRequest $request) {
        $json = $request->getJson();
        $obj = json_decode($json);
        $c = new Category(0);
        $c->setName($obj->name);
        $ok = $this->categories->save($c); 
        return $ok ? $c : false;
    }
   
}

?>