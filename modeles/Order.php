<?php
/**
 * ===========================================
 * Modèle Order - Gestion des commandes
 * ===========================================
 * 
 * User Stories concernées :
 * - US-03 : Achat et validation de commande
 * - US-08 : Visualisation des commandes (Admin)
 * - US-19 : Historique des commandes (Client)
 * - US-32 : Vérification achat pour téléchargement
 * - US-33 : Calcul CA vendeur (Musicien)
 * 
 * Status : pending, paid, shipped, cancelled
 */
class Order {
    private $conn;
    private $table_name = "orders";
    private $items_table = "order_items";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer une commande
    public function create($user_id, $total_amount, $items) {
        try {
            $this->conn->beginTransaction();

            // 1. Créer la commande
            $query = "INSERT INTO " . $this->table_name . " (user_id, total_amount, status, created_at) VALUES (:user_id, :total_amount, 'paid', NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':total_amount', $total_amount);
            $stmt->execute();
            
            $order_id = $this->conn->lastInsertId();

            // 2. Ajouter les items
            $query_item = "INSERT INTO " . $this->items_table . " (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";
            $stmt_item = $this->conn->prepare($query_item);

            foreach ($items as $item) {
                $stmt_item->bindParam(':order_id', $order_id);
                $stmt_item->bindParam(':product_id', $item['product_id']);
                $stmt_item->bindParam(':quantity', $item['quantity']);
                $stmt_item->bindParam(':price', $item['price']);
                $stmt_item->execute();

                // Décrémenter le stock (US-03)
                $query_stock = "UPDATE products SET stock = stock - :quantity WHERE id = :product_id";
                $stmt_stock = $this->conn->prepare($query_stock);
                $stmt_stock->bindParam(':quantity', $item['quantity']);
                $stmt_stock->bindParam(':product_id', $item['product_id']);
                $stmt_stock->execute();
            }

            $this->conn->commit();
            return $order_id;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Récupérer les commandes d'un utilisateur (US-19)
    public function getOrdersByUser($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer toutes les commandes (Admin)
    public function getAllOrders() {
        $query = "SELECT o.*, u.username FROM " . $this->table_name . " o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les détails d'une commande
    public function getOrderDetails($order_id) {
        $query = "SELECT oi.*, p.name as product_name 
                  FROM " . $this->items_table . " oi 
                  LEFT JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Calculer le CA total d'un vendeur (US-33)
    public function getSalesBySeller($seller_id) {
        $query = "SELECT SUM(oi.price * oi.quantity) as total_sales 
                  FROM " . $this->items_table . " oi
                  JOIN products p ON oi.product_id = p.id
                  JOIN " . $this->table_name . " o ON oi.order_id = o.id
                  WHERE p.seller_id = :seller_id AND o.status = 'paid'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':seller_id', $seller_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_sales'] ? $row['total_sales'] : 0;
    }

    // Vérifier si un utilisateur a acheté un produit (US-32)
    public function hasUserBoughtProduct($user_id, $product_id) {
        $query = "SELECT COUNT(*) as count 
                  FROM " . $this->items_table . " oi
                  JOIN " . $this->table_name . " o ON oi.order_id = o.id
                  WHERE o.user_id = :user_id 
                  AND oi.product_id = :product_id 
                  AND o.status = 'paid'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }
}
?>
