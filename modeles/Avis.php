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
/**
 * Class Avis
 *
 * Gestion des avis/notes laissés par les utilisateurs sur les produits.
 * Règle métier : un avis ne doit être autorisé que si l'utilisateur a acheté le produit.
 *
 * @package Modeles
 */
class Avis {
    private $conn;
    private $table_name = "reviews";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crée un avis pour un produit.
     *
     * @param int $product_id
     * @param int $user_id
     * @param int $rating 1..5
     * @param string $comment
     * @return bool
     */
    public function create($product_id, $user_id, $rating, $comment) {
        $query = "INSERT INTO " . $this->table_name . " (product_id, user_id, rating, comment, created_at) VALUES (:product_id, :user_id, :rating, :comment, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':comment', $comment);
        return $stmt->execute();
    }

    /**
     * Récupère la liste des avis d'un produit.
     *
     * @param int $product_id
     * @return array
     */
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

    /**
     * Calcule la note moyenne d'un produit (arrondie à une décimale).
     *
     * @param int $product_id
     * @return float
     */
    public function getAverageRating($product_id) {
        $query = "SELECT AVG(rating) as avg_rating FROM " . $this->table_name . " WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
    }

    /**
     * Crée un avis depuis `$_POST` (encapsulation des données utilisateur).
     *
     * @return bool
     */
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
