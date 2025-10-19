<?php
// Assurez-vous que les chemins sont corrects selon votre arborescence
require_once "src/Controller/EntityController.php"; 
require_once "src/Repository/ProductImageRepository.php" ;
require_once "src/Class/ProductImage.php"; 

/**
 * Class ProductImageController
 * * Gère les requêtes HTTP (GET, POST, DELETE) pour les entités ProductImage.
 * Hérite de la logique de base du traitement des requêtes de EntityController.
 */
class ProductImageController extends EntityController {

    private ProductImageRepository $productImages;

    public function __construct(){
        $this->productImages = new ProductImageRepository();
    }

    //---------------------------------------------------------
    // Traitement des requêtes GET (Lecture)
    //---------------------------------------------------------
    protected function processGetRequest(HttpRequest $request) {
        $id = $request->getId();                 // <-- sans argument
        $productId = $request->getParam("product_id");

        if ($id) {
            $image = $this->productImages->find($id);
            return $image === null ? false : $image;
        } else if ($productId) {
            return $this->productImages->findAllByProduct((int)$productId);
        } else {
            return $this->productImages->findAll();
        }
    }

    //---------------------------------------------------------
    // Traitement des requêtes POST (Création)
    //---------------------------------------------------------
    protected function processPostRequest(HttpRequest $request) {
        $json = $request->getJson();
        $obj = json_decode($json);

        if (!isset($obj->product_id) || !isset($obj->url)) {
            // Pas de jsonResponse ici -> retourner false selon le contrat actuel
            return false;
        }

        $image = new ProductImage(0);
        $image->setProductId((int)$obj->product_id);
        $image->setUrl((string)$obj->url);

        $ok = $this->productImages->save($image); 
        return $ok ? $image : false;
    }

    //---------------------------------------------------------
    // Traitement des requêtes DELETE (Suppression)
    //---------------------------------------------------------
    protected function processDeleteRequest(HttpRequest $request) {
        $id = $request->getId();                 // <-- sans argument
        if (!$id) {
            return false;
        }
        $ok = $this->productImages->delete((int)$id);
        // Retourner un booléen (json_encode(true|false))
        return $ok ? true : false;
    }

}
?>