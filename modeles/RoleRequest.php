<?php
/**
 * ===========================================
 * Modèle RoleRequest - Demandes de rôle
 * ===========================================
 * 
 * User Stories concernées :
 * - US-39 : Validation demande de rôle (Visiteur -> Rédacteur/Musicien)
 * 
 * Workflow : Demande -> Examen -> Validation -> Changement de rôle
 * Status : pending, approved, rejected
 */
class RoleRequest {
    private $conn;
    private $table_name = "role_requests";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Créer une nouvelle demande de rôle
     */
    public function create($user_id, $requested_role, $motivation) {
        // Vérifier si une demande en attente existe déjà
        if ($this->hasPendingRequest($user_id)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, requested_role, motivation, status, created_at) 
                  VALUES (:user_id, :requested_role, :motivation, 'pending', NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':requested_role', $requested_role);
        $stmt->bindParam(':motivation', $motivation);
        
        return $stmt->execute();
    }

    /**
     * Vérifier si l'utilisateur a une demande en attente
     */
    public function hasPendingRequest($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }

    /**
     * Récupérer toutes les demandes en attente
     */
    public function getPendingRequests() {
        $query = "SELECT rr.*, u.username, u.email 
                  FROM " . $this->table_name . " rr
                  JOIN users u ON rr.user_id = u.id
                  WHERE rr.status = 'pending'
                  ORDER BY rr.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer toutes les demandes (historique)
     */
    public function getAllRequests() {
        $query = "SELECT rr.*, u.username, u.email, admin.username as processed_by_name
                  FROM " . $this->table_name . " rr
                  JOIN users u ON rr.user_id = u.id
                  LEFT JOIN users admin ON rr.processed_by = admin.id
                  ORDER BY rr.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Approuver une demande (change le rôle de l'utilisateur)
     */
    public function approve($request_id, $admin_id) {
        // Récupérer la demande
        $query = "SELECT user_id, requested_role FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $request_id);
        $stmt->execute();
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$request) return false;

        // Mettre à jour le rôle de l'utilisateur
        $updateUser = "UPDATE users SET role = :role WHERE id = :user_id";
        $stmtUser = $this->conn->prepare($updateUser);
        $stmtUser->bindParam(':role', $request['requested_role']);
        $stmtUser->bindParam(':user_id', $request['user_id']);
        $stmtUser->execute();

        // Mettre à jour le statut de la demande
        $updateRequest = "UPDATE " . $this->table_name . " 
                          SET status = 'approved', processed_at = NOW(), processed_by = :admin_id 
                          WHERE id = :id";
        $stmtRequest = $this->conn->prepare($updateRequest);
        $stmtRequest->bindParam(':admin_id', $admin_id);
        $stmtRequest->bindParam(':id', $request_id);
        
        return $stmtRequest->execute();
    }

    /**
     * Rejeter une demande
     */
    public function reject($request_id, $admin_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'rejected', processed_at = NOW(), processed_by = :admin_id 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':admin_id', $admin_id);
        $stmt->bindParam(':id', $request_id);
        return $stmt->execute();
    }

    /**
     * Récupérer les demandes d'un utilisateur
     */
    public function getRequestsByUser($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Créer une demande depuis les données POST (encapsulation)
     */
    public function createFromPost($user_id) {
        $requested_role = isset($_POST['requested_role']) ? htmlspecialchars($_POST['requested_role']) : '';
        $motivation = isset($_POST['motivation']) ? htmlspecialchars($_POST['motivation']) : '';
        
        if (!empty($requested_role)) {
            return $this->create($user_id, $requested_role, $motivation);
        }
        return false;
    }
}
?>
