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
        $itemReq = $this->cnx->prepare("SELECT variant_id, quantite, prix_unitaire FROM CommandePanierItem WHERE commande_id = :commandeId");
        $itemReq->bindParam(':commandeId', $row->id, PDO::PARAM_INT);
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
        $req = $this->cnx->prepare("SELECT id, client_id FROM CommandePanier WHERE client_id = :clientId AND statut = 'Panier' LIMIT 1");
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

            $sel = $this->cnx->prepare("SELECT id FROM CommandePanier WHERE client_id = :clientId AND statut = 'Panier' LIMIT 1");
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

            $del = $this->cnx->prepare("DELETE FROM CommandePanierItem WHERE commande_id = :panierId");
            $del->bindParam(':panierId', $panierId, PDO::PARAM_INT);
            $del->execute();

            $insItem = $this->cnx->prepare("INSERT INTO CommandePanierItem (commande_id, variant_id, quantite, prix_unitaire) VALUES (:panierId, :variantId, :quantite, :prixUnitaire)");
            foreach ($items as $it) {
                $variantId = (int)($it['variantId'] ?? $it['productId'] ?? 0);
                $quantity = (int)($it['quantity'] ?? $it['quantite'] ?? 0);
                $unitPrice = isset($it['unitPrice']) ? (float)$it['unitPrice'] : (isset($it['prix_unitaire']) ? (float)$it['prix_unitaire'] : null);
                
                // Si le prix est null ou 0, essayer de le récupérer depuis la table Product
                if ($unitPrice === null || $unitPrice == 0) {
                    try {
                        $priceReq = $this->cnx->prepare("SELECT price FROM Product WHERE id = :productId LIMIT 1");
                        $priceReq->bindValue(':productId', $variantId, PDO::PARAM_INT);
                        $priceReq->execute();
                        $priceRow = $priceReq->fetch(PDO::FETCH_OBJ);
                        if ($priceRow && $priceRow->price) {
                            $unitPrice = (float)$priceRow->price;
                        }
                    } catch (Exception $e) {
                        // Ignorer l'erreur et continuer avec prix null
                    }
                }
                
                if ($variantId <= 0 || $quantity <= 0) continue;
                $insItem->bindValue(':panierId', $panierId, PDO::PARAM_INT);
                $insItem->bindValue(':variantId', $variantId, PDO::PARAM_INT);
                $insItem->bindValue(':quantite', $quantity, PDO::PARAM_INT);
                if ($unitPrice === null || $unitPrice == 0) {
                    $insItem->bindValue(':prixUnitaire', 0.00);
                } else {
                    $insItem->bindValue(':prixUnitaire', $unitPrice);
                }
                $insItem->execute();
            }

            $this->cnx->commit();
            return true;
        } catch (Exception $e) {
            if ($this->cnx->inTransaction()) $this->cnx->rollBack();
            error_log("CartRepository::save ERROR: " . $e->getMessage());
            error_log("CartRepository::save clientId: " . $clientId . ", items count: " . count($items));
            return false;
        }
    }

    // delete by cart id
    public function delete($id): bool {
        try {
            $this->cnx->beginTransaction();
            $delItems = $this->cnx->prepare("DELETE FROM CommandePanierItem WHERE commande_id = :panierId");
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
            $sel = $this->cnx->prepare("SELECT id FROM CommandePanier WHERE client_id = :clientId AND statut = 'Panier' LIMIT 1");
            $sel->bindParam(':clientId', $userId, PDO::PARAM_INT);
            $sel->execute();
            $found = $sel->fetch(PDO::FETCH_OBJ);
            if ($found != false) {
                $panierId = (int)$found->id;
                $delItems = $this->cnx->prepare("DELETE FROM CommandePanierItem WHERE commande_id = :panierId");
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

    /**
     * Valider le panier en commande - US007
     * Transforme un panier en commande avec un numéro unique
     */
    public function validateOrder(int $userId): ?array {
        try {
            $this->cnx->beginTransaction();
            
            // 1. Trouver le panier actif de l'utilisateur
            $cartReq = $this->cnx->prepare("
                SELECT id FROM CommandePanier 
                WHERE client_id = :clientId AND statut = 'Panier'
                LIMIT 1
            ");
            $cartReq->bindParam(':clientId', $userId, PDO::PARAM_INT);
            $cartReq->execute();
            $cartRow = $cartReq->fetch(PDO::FETCH_OBJ);
            
            if ($cartRow === false) {
                $this->cnx->rollBack();
                return null; // Pas de panier trouvé
            }
            
            $panierId = (int)$cartRow->id;
            
            // 2. Vérifier que le panier contient des items
            $itemsReq = $this->cnx->prepare("
                SELECT COUNT(*) as count FROM CommandePanierItem 
                WHERE commande_id = :panierId
            ");
            $itemsReq->bindParam(':panierId', $panierId, PDO::PARAM_INT);
            $itemsReq->execute();
            $itemsCount = $itemsReq->fetch(PDO::FETCH_OBJ);
            
            if ($itemsCount->count == 0) {
                $this->cnx->rollBack();
                return null; // Panier vide
            }
            
            // 3. Calculer le montant total
            $totalReq = $this->cnx->prepare("
                SELECT SUM(quantite * prix_unitaire) as total 
                FROM CommandePanierItem 
                WHERE commande_id = :panierId
            ");
            $totalReq->bindParam(':panierId', $panierId, PDO::PARAM_INT);
            $totalReq->execute();
            $totalRow = $totalReq->fetch(PDO::FETCH_OBJ);
            $totalAmount = $totalRow->total ? (float)$totalRow->total : 0.0;
            
            // 4. Vérifier si les colonnes order_number et date_commande existent
            $hasOrderColumns = false;
            try {
                $checkReq = $this->cnx->query("SHOW COLUMNS FROM CommandePanier LIKE 'order_number'");
                $hasOrderColumns = $checkReq->rowCount() > 0;
            } catch (Exception $e) {
                $hasOrderColumns = false;
            }
            
            // 5. Transformer le panier en commande
            if ($hasOrderColumns) {
                // Avec colonnes order_number et date_commande
                $orderNumber = $this->generateOrderNumber();
                $updateReq = $this->cnx->prepare("
                    UPDATE CommandePanier 
                    SET statut = 'Commande', 
                        order_number = :orderNumber,
                        date_commande = NOW(),
                        montant_total = :montantTotal
                    WHERE id = :panierId
                ");
                $updateReq->bindParam(':orderNumber', $orderNumber);
                $updateReq->bindParam(':montantTotal', $totalAmount);
                $updateReq->bindParam(':panierId', $panierId, PDO::PARAM_INT);
            } else {
                // Sans colonnes order_number et date_commande (version basique)
                $updateReq = $this->cnx->prepare("
                    UPDATE CommandePanier 
                    SET statut = 'Commande', 
                        montant_total = :montantTotal
                    WHERE id = :panierId
                ");
                $updateReq->bindParam(':montantTotal', $totalAmount);
                $updateReq->bindParam(':panierId', $panierId, PDO::PARAM_INT);
            }
            
            if (!$updateReq->execute()) {
                $this->cnx->rollBack();
                return null;
            }
            
            $this->cnx->commit();
            
            // 6. Retourner la commande créée (avec ou sans order_number)
            if ($hasOrderColumns) {
                return $this->getOrderDetails($panierId);
            } else {
                return $this->getOrderDetailsBasic($panierId);
            }
            
        } catch (Exception $e) {
            if ($this->cnx->inTransaction()) {
                $this->cnx->rollBack();
            }
            error_log("CartRepository::validateOrder ERROR: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupérer les détails d'une commande
     */
    public function getOrderDetails(int $orderId): ?array {
        $req = $this->cnx->prepare("
            SELECT id, order_number, client_id, statut, montant_total, date_commande, date_sauvegarde 
            FROM CommandePanier 
            WHERE id = :id AND statut = 'Commande'
            LIMIT 1
        ");
        $req->bindParam(':id', $orderId, PDO::PARAM_INT);
        $req->execute();
        $row = $req->fetch(PDO::FETCH_OBJ);
        
        if ($row === false) {
            return null;
        }
        
        // Charger les items de la commande
        $itemsReq = $this->cnx->prepare("
            SELECT 
                cpi.id,
                cpi.variant_id,
                cpi.quantite,
                cpi.prix_unitaire,
                p.name as product_name
            FROM CommandePanierItem cpi
            LEFT JOIN Product p ON p.id = cpi.variant_id
            WHERE cpi.commande_id = :panierId
        ");
        $itemsReq->bindParam(':panierId', $orderId, PDO::PARAM_INT);
        $itemsReq->execute();
        
        $items = [];
        while ($itemRow = $itemsReq->fetch(PDO::FETCH_OBJ)) {
            $items[] = [
                'id' => (int)$itemRow->id,
                'variantId' => (int)$itemRow->variant_id,
                'productName' => $itemRow->product_name ?? 'Produit',
                'quantity' => (int)$itemRow->quantite,
                'unitPrice' => (float)$itemRow->prix_unitaire,
                'totalPrice' => (float)$itemRow->quantite * (float)$itemRow->prix_unitaire
            ];
        }
        
        return [
            'id' => (int)$row->id,
            'orderNumber' => $row->order_number ?? '',
            'clientId' => (int)$row->client_id,
            'status' => $row->statut,
            'totalAmount' => (float)$row->montant_total,
            'dateCommande' => $row->date_commande,
            'dateSauvegarde' => $row->date_sauvegarde,
            'items' => $items
        ];
    }

    /**
     * Récupérer les détails d'une commande (version basique sans order_number)
     */
    public function getOrderDetailsBasic(int $orderId): ?array {
        $req = $this->cnx->prepare("
            SELECT id, client_id, statut, montant_total, date_sauvegarde 
            FROM CommandePanier 
            WHERE id = :id AND statut = 'Commande'
            LIMIT 1
        ");
        $req->bindParam(':id', $orderId, PDO::PARAM_INT);
        $req->execute();
        $row = $req->fetch(PDO::FETCH_OBJ);
        
        if ($row === false) {
            return null;
        }
        
        // Charger les items de la commande
        $itemsReq = $this->cnx->prepare("
            SELECT 
                cpi.id,
                cpi.variant_id,
                cpi.quantite,
                cpi.prix_unitaire,
                p.name as product_name
            FROM CommandePanierItem cpi
            LEFT JOIN Product p ON p.id = cpi.variant_id
            WHERE cpi.commande_id = :panierId
        ");
        $itemsReq->bindParam(':panierId', $orderId, PDO::PARAM_INT);
        $itemsReq->execute();
        
        $items = [];
        while ($itemRow = $itemsReq->fetch(PDO::FETCH_OBJ)) {
            $items[] = [
                'id' => (int)$itemRow->id,
                'variantId' => (int)$itemRow->variant_id,
                'productName' => $itemRow->product_name ?? 'Produit',
                'quantity' => (int)$itemRow->quantite,
                'unitPrice' => (float)$itemRow->prix_unitaire,
                'totalPrice' => (float)$itemRow->quantite * (float)$itemRow->prix_unitaire
            ];
        }
        
        return [
            'id' => (int)$row->id,
            'orderNumber' => 'CMD-' . str_pad($row->id, 8, '0', STR_PAD_LEFT), // Numéro basé sur l'ID
            'clientId' => (int)$row->client_id,
            'status' => $row->statut,
            'totalAmount' => (float)$row->montant_total,
            'dateCommande' => $row->date_sauvegarde, // Utilise date_sauvegarde comme date de commande
            'dateSauvegarde' => $row->date_sauvegarde,
            'items' => $items
        ];
    }

    /**
     * Récupérer toutes les commandes d'un utilisateur
     */
    public function getUserOrders(int $userId): array {
        $req = $this->cnx->prepare("
            SELECT id FROM CommandePanier 
            WHERE client_id = :clientId AND statut = 'Commande'
            ORDER BY date_commande DESC
        ");
        $req->bindParam(':clientId', $userId, PDO::PARAM_INT);
        $req->execute();
        
        $orders = [];
        while ($row = $req->fetch(PDO::FETCH_OBJ)) {
            $order = $this->getOrderDetails((int)$row->id);
            if ($order !== null) {
                $orders[] = $order;
            }
        }
        
        return $orders;
    }

    /**
     * Générer un numéro de commande unique
     * Format: CMD-YYYYMMDD-XXXXXX
     */
    private function generateOrderNumber(): string {
        $prefix = 'CMD';
        $date = date('Ymd');
        $random = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $orderNumber = $prefix . '-' . $date . '-' . $random;
        
        // Vérifier l'unicité
        $check = $this->cnx->prepare("SELECT id FROM CommandePanier WHERE order_number = :orderNumber");
        $check->bindParam(':orderNumber', $orderNumber);
        $check->execute();
        
        // Si le numéro existe déjà, on réessaye
        if ($check->fetch() !== false) {
            return $this->generateOrderNumber();
        }
        
        return $orderNumber;
    }
}
?>