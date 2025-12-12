<?php
/**
 * ===========================================
 * Modèle Commentaire - Gestion des commentaires
 * ===========================================
 * 
 * User Stories concernées :
 * - US-24 : Gestion des commentaires (Rédacteur/Admin)
 * - US-35 : Ajout de commentaire par visiteur connecté
 * 
 * Status : pending, approved, rejected
 * Les commentaires sont soumis à modération avant publication.
 */
class Commentaire {
    private $conn;
    private $table_name = "comments";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer un commentaire
    public function create($article_id, $user_id, $content) {
        $query = "INSERT INTO " . $this->table_name . " (article_id, user_id, content, status, created_at) VALUES (:article_id, :user_id, :content, 'pending', NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':content', $content);
        return $stmt->execute();
    }

    // Lire les commentaires validés d'un article
    public function getCommentsByArticle($article_id) {
        $query = "SELECT c.*, u.username FROM " . $this->table_name . " c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.article_id = :article_id AND c.status = 'approved' 
                  ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Lire les commentaires d'un utilisateur avec leur statut (pour afficher l'état)
    public function getUserCommentsByArticle($article_id, $user_id) {
        $query = "SELECT c.*, u.username FROM " . $this->table_name . " c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.article_id = :article_id AND c.user_id = :user_id 
                  ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lire tous les commentaires (pour modération admin)
    public function getAllComments() {
        $query = "SELECT c.*, u.username, a.title as article_title, a.author_id as article_author_id 
                  FROM " . $this->table_name . " c 
                  JOIN users u ON c.user_id = u.id 
                  JOIN articles a ON c.article_id = a.id 
                  ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lire les commentaires sur les articles d'un auteur spécifique (pour rédacteur)
    public function getCommentsByArticleAuthor($author_id) {
        $query = "SELECT c.*, u.username, a.title as article_title, a.author_id as article_author_id 
                  FROM " . $this->table_name . " c 
                  JOIN users u ON c.user_id = u.id 
                  JOIN articles a ON c.article_id = a.id 
                  WHERE a.author_id = :author_id
                  ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':author_id', $author_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Vérifier si un commentaire appartient à un article d'un auteur
    public function isCommentOnAuthorArticle($comment_id, $author_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " c 
                  JOIN articles a ON c.article_id = a.id 
                  WHERE c.id = :comment_id AND a.author_id = :author_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':comment_id', $comment_id);
        $stmt->bindParam(':author_id', $author_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }

    // Modérer un commentaire
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Supprimer un commentaire
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Créer un commentaire depuis les données POST (encapsulation)
    public function createFromPost() {
        $article_id = isset($_POST['article_id']) ? intval($_POST['article_id']) : 0;
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $content = isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '';
        
        if ($article_id > 0 && $user_id > 0 && !empty($content)) {
            return $this->create($article_id, $user_id, $content);
        }
        return false;
    }
}
?>

