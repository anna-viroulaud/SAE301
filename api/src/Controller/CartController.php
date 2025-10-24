<?php

require_once "src/Controller/EntityController.php";
require_once "src/Repository/CartRepository.php";
require_once "src/Class/Cart.php";

/**
 * CartController - endpoints nécessaires pour US006
 * - GET /api/carts?clientId=...
 * - POST /api/carts  (payload { clientId|userId, items })
 * - DELETE /api/carts?clientId=...
 */
class CartController extends EntityController {

    private CartRepository $repo;

    public function __construct(){
        $this->repo = new CartRepository();
    }

    protected function processGetRequest(HttpRequest $request) {
        $clientId = $request->getParam('clientId') ?? $request->getParam('userId');
        if ($clientId) {
            $c = $this->repo->findByUserId((int)$clientId);
            if ($c === null) {
                http_response_code(200);
                return ["clientId" => (int)$clientId, "items" => []];
            }
            return $c;
        }

        http_response_code(400);
        return ["error" => "clientId required"];
    }

    protected function processPostRequest(HttpRequest $request) {
        $json = $request->getJson();
        $obj = $json ? json_decode($json, true) : null;
        if ($obj === null) {
            http_response_code(400);
            return ["error" => "Invalid payload"];
        }

        $clientId = isset($obj['clientId']) ? (int)$obj['clientId'] : (isset($obj['userId']) ? (int)$obj['userId'] : 0);
        $items = isset($obj['items']) && is_array($obj['items']) ? $obj['items'] : [];

        if ($clientId <= 0) {
            http_response_code(400);
            return ["error" => "clientId required"];
        }

        $cart = new Cart($clientId, $items);
        $ok = $this->repo->save($cart);
        if (!$ok) {
            http_response_code(500);
            return ["error" => "Cannot save cart"];
        }

        http_response_code(201);
        return ["message" => "saved"];
    }

    protected function processDeleteRequest(HttpRequest $request) {
        $clientId = $request->getParam('clientId') ?? $request->getParam('userId');
        if (!$clientId) {
            http_response_code(400);
            return ["error" => "clientId required"];
        }
        $ok = $this->repo->deleteByUserId((int)$clientId);
        if (!$ok) { http_response_code(500); return ["error" => "Cannot delete cart"]; }
        http_response_code(204);
        return ["message" => "deleted"];
    }

    protected function processPutRequest(HttpRequest $request) { http_response_code(405); return ["error" => "Method not allowed"]; }
    protected function processPatchRequest(HttpRequest $request) { http_response_code(405); return ["error" => "Method not allowed"]; }
}
?>