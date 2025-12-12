<?php
/**
 * ===========================================
 * Modèle Notification - Centre de notifications
 * ===========================================
 * 
 * User Stories concernées :
 * - US-29 : Notifications pour nouvelles annonces
 * 
 * États : Non lu / Lu / Archivé
 * Notifie les admins des nouvelles actions à modérer.
 */
class Notification {
    private $conn;
    private $table_name = "notifications";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer une notification
    public function create($user_id, $message, $link = null) {
        $query = "INSERT INTO " . $this->table_name . " (user_id, message, link, is_read, created_at) 
                  VALUES (:user_id, :message, :link, 0, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':link', $link);
        return $stmt->execute();
    }

    // Récupérer les notifications d'un utilisateur
    public function getByUser($user_id, $limit = 20) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC 
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer toutes les notifications (pour admin global)
    public function getAll($limit = 50) {
        $query = "SELECT n.*, u.username 
                  FROM " . $this->table_name . " n
                  LEFT JOIN users u ON n.user_id = u.id
                  ORDER BY n.created_at DESC 
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les notifications non lues
    public function getUnreadByUser($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND is_read = 0 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Compter les notifications non lues
    public function countUnread($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND is_read = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    // Marquer comme lu
    public function markAsRead($id) {
        $query = "UPDATE " . $this->table_name . " SET is_read = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Marquer toutes comme lues pour un utilisateur
    public function markAllAsRead($user_id) {
        $query = "UPDATE " . $this->table_name . " SET is_read = 1 WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }

    // Supprimer une notification
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Notifier les admins (helper)
    public function notifyAdmins($message, $link = null) {
        // Récupérer tous les admins
        $query = "SELECT id FROM users WHERE role = 'admin'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($admins as $admin) {
            $this->create($admin['id'], $message, $link);
        }
        return true;
    }
}
?>
