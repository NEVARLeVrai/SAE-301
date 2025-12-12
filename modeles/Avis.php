<?php
/**
 * ===========================================
 * Modèle Avis - Notation et avis produits
 * ===========================================
 * 
 * User Stories concernées :
 * - US-21 : Système de notation/avis (1 à 5 étoiles)
 * 
 * Règle : Seuls les clients ayant acheté peuvent laisser un avis.
 */
class Avis {
    private $conn;
    private $table_name = "reviews";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer un avis
    public function create($product_id, $user_id, $rating, $comment) {
        $query = "INSERT INTO " . $this->table_name . " (product_id, user_id, rating, comment, created_at) VALUES (:product_id, :user_id, :rating, :comment, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':comment', $comment);
        return $stmt->execute();
    }

    // Lire les avis d'un produit
    public function getReviewsByProduct($product_id) {
        $query = "SELECT r.*, u.username FROM " . $this->table_name . " r 
                  JOIN users u ON r.user_id = u.id 
                  WHERE r.product_id = :product_id 
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Calculer la moyenne des notes
    public function getAverageRating($product_id) {
        $query = "SELECT AVG(rating) as avg_rating FROM " . $this->table_name . " WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
    }

    // Créer un avis depuis les données POST (encapsulation)
    public function createFromPost() {
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        $comment = isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : '';
        
        if ($product_id > 0 && $user_id > 0 && $rating > 0 && $rating <= 5) {
            return $this->create($product_id, $user_id, $rating, $comment);
        }
        return false;
    }
}
?>
