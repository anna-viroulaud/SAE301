<?php

require_once("src/Repository/EntityRepository.php");
require_once("src/Class/Cart.php");

/**
 * CartRepository - accès aux tables CommandePanier / CommandePanierItem
 */
class CartRepository extends EntityRepository {

    public function __construct(){
        parent::__construct();
    }

    // find by cart id
    public function find($id): ?Cart {
        $req = $this->cnx->prepare("SELECT id, client_id FROM CommandePanier WHERE id = :id LIMIT 1");
        $req->bindParam(':id', $id, PDO::PARAM_INT);
        $req->execute();
        $row = $req->fetch(PDO::FETCH_OBJ);
        if ($row === false) return null;

        $items = [];
        $itemReq = $this->cnx->prepare("SELECT variant_id, quantite, prix_unitaire FROM CommandePanierItem WHERE panier_id = :panierId");
        $itemReq->bindParam(':panierId', $row->id, PDO::PARAM_INT);
        $itemReq->execute();
        while ($it = $itemReq->fetch(PDO::FETCH_OBJ)) {
            $items[] = [
                'variantId' => (int)$it->variant_id,
                'quantity'  => (int)$it->quantite,
                'unitPrice' => $it->prix_unitaire !== null ? (float)$it->prix_unitaire : null
            ];
        }

        return new Cart((int)$row->client_id, $items);
    }

    // find by client/user id (helper)
    public function findByUserId($userId): ?Cart {
        $req = $this->cnx->prepare("SELECT id, client_id FROM CommandePanier WHERE client_id = :clientId LIMIT 1");
        $req->bindParam(':clientId', $userId, PDO::PARAM_INT);
        $req->execute();
        $row = $req->fetch(PDO::FETCH_OBJ);
        if ($row === false) return null;
        return $this->find((int)$row->id);
    }

    // find all carts
    public function findAll(): array {
        $req = $this->cnx->prepare("SELECT id FROM CommandePanier");
        $req->execute();
        $rows = $req->fetchAll(PDO::FETCH_OBJ);
        $res = [];
        foreach ($rows as $r) {
            $c = $this->find((int)$r->id);
            if ($c !== null) $res[] = $c;
        }
        return $res;
    }

    // save or update (exists already)
    public function save($cart): bool {
        $clientId = method_exists($cart, 'getUserId') ? $cart->getUserId() : ($cart->userId ?? 0);
        $items = method_exists($cart, 'getItems') ? $cart->getItems() : ($cart->items ?? []);
        if ($clientId <= 0) return false;

        try {
            $this->cnx->beginTransaction();

            $sel = $this->cnx->prepare("SELECT id FROM CommandePanier WHERE client_id = :clientId LIMIT 1");
            $sel->bindParam(':clientId', $clientId, PDO::PARAM_INT);
            $sel->execute();
            $found = $sel->fetch(PDO::FETCH_OBJ);

            if ($found == false) {
                $ins = $this->cnx->prepare("INSERT INTO CommandePanier (client_id, date_sauvegarde, statut, montant_total) VALUES (:clientId, NOW(), 'Panier', 0)");
                $ins->bindParam(':clientId', $clientId, PDO::PARAM_INT);
                if (!$ins->execute()) { $this->cnx->rollBack(); return false; }
                $panierId = (int)$this->cnx->lastInsertId();
            } else {
                $panierId = (int)$found->id;
            }

            $del = $this->cnx->prepare("DELETE FROM CommandePanierItem WHERE panier_id = :panierId");
            $del->bindParam(':panierId', $panierId, PDO::PARAM_INT);
            $del->execute();

            $insItem = $this->cnx->prepare("INSERT INTO CommandePanierItem (panier_id, variant_id, quantite, prix_unitaire) VALUES (:panierId, :variantId, :quantite, :prixUnitaire)");
            foreach ($items as $it) {
                $variantId = (int)($it['variantId'] ?? $it['productId'] ?? 0);
                $quantity = (int)($it['quantity'] ?? $it['quantite'] ?? 0);
                $unitPrice = isset($it['unitPrice']) ? (float)$it['unitPrice'] : (isset($it['prix_unitaire']) ? (float)$it['prix_unitaire'] : null);
                if ($variantId <= 0 || $quantity <= 0) continue;
                $insItem->bindValue(':panierId', $panierId, PDO::PARAM_INT);
                $insItem->bindValue(':variantId', $variantId, PDO::PARAM_INT);
                $insItem->bindValue(':quantite', $quantity, PDO::PARAM_INT);
                if ($unitPrice === null) {
                    $insItem->bindValue(':prixUnitaire', null, PDO::PARAM_NULL);
                } else {
                    $insItem->bindValue(':prixUnitaire', $unitPrice);
                }
                $insItem->execute();
            }

            $this->cnx->commit();
            return true;
        } catch (Exception $e) {
            if ($this->cnx->inTransaction()) $this->cnx->rollBack();
            return false;
        }
    }

    // delete by cart id
    public function delete($id): bool {
        try {
            $this->cnx->beginTransaction();
            $delItems = $this->cnx->prepare("DELETE FROM CommandePanierItem WHERE panier_id = :panierId");
            $delItems->bindParam(':panierId', $id, PDO::PARAM_INT);
            $delItems->execute();

            $delPanier = $this->cnx->prepare("DELETE FROM CommandePanier WHERE id = :panierId");
            $delPanier->bindParam(':panierId', $id, PDO::PARAM_INT);
            $delPanier->execute();

            $this->cnx->commit();
            return true;
        } catch (Exception $e) {
            if ($this->cnx->inTransaction()) $this->cnx->rollBack();
            return false;
        }
    }

    // delete by user/client id
    public function deleteByUserId($userId): bool {
        try {
            $this->cnx->beginTransaction();
            $sel = $this->cnx->prepare("SELECT id FROM CommandePanier WHERE client_id = :clientId LIMIT 1");
            $sel->bindParam(':clientId', $userId, PDO::PARAM_INT);
            $sel->execute();
            $found = $sel->fetch(PDO::FETCH_OBJ);
            if ($found != false) {
                $panierId = (int)$found->id;
                $delItems = $this->cnx->prepare("DELETE FROM CommandePanierItem WHERE panier_id = :panierId");
                $delItems->bindParam(':panierId', $panierId, PDO::PARAM_INT);
                $delItems->execute();

                $delPanier = $this->cnx->prepare("DELETE FROM CommandePanier WHERE id = :panierId");
                $delPanier->bindParam(':panierId', $panierId, PDO::PARAM_INT);
                $delPanier->execute();
            }
            $this->cnx->commit();
            return true;
        } catch (Exception $e) {
            if ($this->cnx->inTransaction()) $this->cnx->rollBack();
            return false;
        }
    }

    // update wrapper
    public function update($cart): bool {
        return $this->save($cart);
    }
}
?>