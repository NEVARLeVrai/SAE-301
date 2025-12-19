<?php
/**
 * ===========================================
 * Modèle ModerationRequest - Modération produits
 * ===========================================
 * 
 * User Stories concernées :
 * - US-04 : Vente instruments d'occasion (soumis à modération)
 * - US-10 : Réception et validation des annonces
 * 
 * Workflow : Annonce soumise -> En attente -> Approuvé/Rejeté
 * Status : pending, approved, rejected
 */
/**
 * Class ModerationRequest
 *
 * Gère les demandes de modération d'annonces produits.
 * Fournit la création, récupération et l'approbation/rejet des demandes.
 *
 * @package Modeles
 */
class ModerationRequest {
    private $conn;
    private $table_name = "moderation_requests";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($user_id, $product_id, $message = null) {
        $query = "INSERT INTO " . $this->table_name . " (user_id, product_id, message, status, created_at) VALUES (:user_id, :product_id, :message, 'pending', NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':message', $message);
        return $stmt->execute();
    }

    public function getPendingRequests() {
        $query = "SELECT mr.*, p.name as product_name, u.username, u.email FROM " . $this->table_name . " mr JOIN products p ON mr.product_id = p.id JOIN users u ON mr.user_id = u.id WHERE mr.status = 'pending' ORDER BY mr.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllRequests() {
        $query = "SELECT mr.*, p.name as product_name, u.username, u.email, admin.username as processed_by_name FROM " . $this->table_name . " mr JOIN products p ON mr.product_id = p.id JOIN users u ON mr.user_id = u.id LEFT JOIN users admin ON mr.processed_by = admin.id ORDER BY mr.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approve($request_id, $admin_id) {
        // Mettre à jour le produit en approved
        $query = "SELECT product_id FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $request_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;

        $product_id = $row['product_id'];
        $updateProduct = "UPDATE products SET status = 'approved' WHERE id = :id";
        $stmtP = $this->conn->prepare($updateProduct);
        $stmtP->bindParam(':id', $product_id);
        $stmtP->execute();

        $update = "UPDATE " . $this->table_name . " SET status = 'approved', processed_at = NOW(), processed_by = :admin WHERE id = :id";
        $stmt2 = $this->conn->prepare($update);
        $stmt2->bindParam(':admin', $admin_id);
        $stmt2->bindParam(':id', $request_id);
        return $stmt2->execute();
    }

    public function reject($request_id, $admin_id) {
        // Optionnel: marquer le produit rejected
        $query = "SELECT product_id FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $request_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $product_id = $row['product_id'];
            $updateProduct = "UPDATE products SET status = 'rejected' WHERE id = :id";
            $stmtP = $this->conn->prepare($updateProduct);
            $stmtP->bindParam(':id', $product_id);
            $stmtP->execute();
        }

        $update = "UPDATE " . $this->table_name . " SET status = 'rejected', processed_at = NOW(), processed_by = :admin WHERE id = :id";
        $stmt2 = $this->conn->prepare($update);
        $stmt2->bindParam(':admin', $admin_id);
        $stmt2->bindParam(':id', $request_id);
        return $stmt2->execute();
    }

    public function getRequestsByUser($user_id) {
        $query = "SELECT mr.*, p.name as product_name FROM " . $this->table_name . " mr JOIN products p ON mr.product_id = p.id WHERE mr.user_id = :user_id ORDER BY mr.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>