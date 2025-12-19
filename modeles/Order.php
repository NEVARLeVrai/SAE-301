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
/**
 * Class Order
 *
 * Gestion des commandes : création, lecture, calculs de ventes.
 * - Utilise une transaction pour créer la commande et ses `order_items`.
 * - Effets de bord : écrit en base et décrémente le stock des produits.
 * - Remarque : la méthode `create()` utilise une transaction mais n'applique
 *   pas de verrouillage row-level (`SELECT ... FOR UPDATE`). En cas de
 *   forte concurrence il est recommandé d'ajouter un verrouillage explicite
 *   sur les lignes `products` avant de décrémenter le stock.
 *
 * @package Modeles
 */
class Order {
    /** @var PDO Connexion PDO vers la base de données */
    private $conn;
    /** @var string Nom de la table orders */
    private $table_name = "orders";
    /** @var string Nom de la table order_items */
    private $items_table = "order_items";

    /**
     * Order constructor.
     * @param PDO $db Instance PDO active
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crée une commande et ses items en base de données.
     * Effectue une transaction et décrémente le stock des produits.
     *
     * @param int $user_id Identifiant de l'utilisateur commandant
     * @param float $total_amount Montant total de la commande
     * @param array $items Liste d'items, chaque item attend : ['product_id', 'quantity', 'price']
     * @return int|false L'ID de la commande créée ou false en cas d'erreur
     */
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

    /**
     * Récupère les commandes d'un utilisateur triées par date décroissante.
     *
     * @param int $user_id Identifiant de l'utilisateur
     * @return array Liste associative des commandes
     */
    public function getOrdersByUser($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer toutes les commandes (Admin)
    /**
     * Récupère toutes les commandes (usage admin) avec le nom d'utilisateur.
     *
     * @return array Liste associative des commandes
     */
    public function getAllOrders() {
        $query = "SELECT o.*, u.username FROM " . $this->table_name . " o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les détails d'une commande
    /**
     * Récupère les items d'une commande et les informations produits associées.
     *
     * @param int $order_id Identifiant de la commande
     * @return array Liste des items de la commande
     */
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
    /**
     * Calcule le chiffre d'affaires total pour un vendeur (toutes commandes payées).
     *
     * @param int $seller_id Identifiant du vendeur
     * @return float Montant total des ventes
     */
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

    /**
     * Vérifie si un utilisateur a acheté un produit donné (utile pour accès aux téléchargements).
     *
     * @param int $user_id Identifiant de l'utilisateur
     * @param int $product_id Identifiant du produit
     * @return bool True si l'utilisateur a au moins une commande payée contenant le produit
     */
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
