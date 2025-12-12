<?php
/**
 * ===========================================
 * Modèle Favori - Gestion des favoris
 * ===========================================
 * 
 * User Stories concernées :
 * - US-16 : Système de favoris (cours et articles)
 * 
 * Stockage en base de données (synchronisation).
 * L'icône change d'état (plein/vide) selon le statut.
 */
class Favori {
    private $conn;
    private $table_name = "favorites";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Ajouter un favori
    public function add($user_id, $item_id, $item_type) {
        // Vérifier si le favori existe déjà
        if ($this->isFavorite($user_id, $item_id, $item_type)) {
            return true; // Déjà en favori
        }

        $query = "INSERT INTO " . $this->table_name . " (user_id, item_id, item_type, created_at) 
                  VALUES (:user_id, :item_id, :item_type, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':item_id', $item_id);
        $stmt->bindParam(':item_type', $item_type);
        return $stmt->execute();
    }

    // Supprimer un favori
    public function remove($user_id, $item_id, $item_type) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND item_id = :item_id AND item_type = :item_type";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':item_id', $item_id);
        $stmt->bindParam(':item_type', $item_type);
        return $stmt->execute();
    }

    // Vérifier si un élément est en favori
    public function isFavorite($user_id, $item_id, $item_type) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND item_id = :item_id AND item_type = :item_type";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':item_id', $item_id);
        $stmt->bindParam(':item_type', $item_type);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }

    // Récupérer les favoris d'un utilisateur par type
    public function getFavoritesByUser($user_id, $item_type = null) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id";
        
        if ($item_type) {
            $query .= " AND item_type = :item_type";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($item_type) {
            $stmt->bindParam(':item_type', $item_type);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les cours favoris avec détails
    public function getFavoriteCoursesWithDetails($user_id) {
        $query = "SELECT c.*, f.created_at as favorited_at, u.username as author
                  FROM " . $this->table_name . " f
                  JOIN courses c ON f.item_id = c.id
                  LEFT JOIN users u ON c.author_id = u.id
                  WHERE f.user_id = :user_id AND f.item_type = 'course'
                  ORDER BY f.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les articles favoris avec détails
    public function getFavoriteArticlesWithDetails($user_id) {
        $query = "SELECT a.*, f.created_at as favorited_at, u.username as author
                  FROM " . $this->table_name . " f
                  JOIN articles a ON f.item_id = a.id
                  LEFT JOIN users u ON a.author_id = u.id
                  WHERE f.user_id = :user_id AND f.item_type = 'article' AND a.is_deleted = 0
                  ORDER BY f.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Compter les favoris d'un utilisateur
    public function countByUser($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
}
?>
