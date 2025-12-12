<?php
/**
 * ===========================================
 * Modèle Tag - Gestion des tags articles
 * ===========================================
 * 
 * User Stories concernées :
 * - US-28 : Gestion des catégories et tags
 * 
 * Relation Many-to-Many via table article_tags.
 * Association multiple possible par article.
 */
class Tag {
    private $conn;
    private $table_name = "tags";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Récupérer tous les tags
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Créer un tag
    public function create($name) {
        $query = "INSERT INTO " . $this->table_name . " (name) VALUES (:name)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Supprimer un tag
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Créer un tag depuis les données POST (encapsulation)
    public function createFromPost() {
        $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
        
        if (!empty($name)) {
            return $this->create($name);
        }
        return false;
    }

    // Récupérer les tags d'un article
    public function getTagsByArticle($article_id) {
        $query = "SELECT t.* FROM " . $this->table_name . " t 
                  JOIN article_tags at ON t.id = at.tag_id 
                  WHERE at.article_id = :article_id 
                  ORDER BY t.name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Associer des tags à un article
    public function setArticleTags($article_id, $tag_ids) {
        // Supprimer les anciens tags
        $deleteQuery = "DELETE FROM article_tags WHERE article_id = :article_id";
        $stmt = $this->conn->prepare($deleteQuery);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->execute();

        // Ajouter les nouveaux tags
        if (!empty($tag_ids) && is_array($tag_ids)) {
            $insertQuery = "INSERT INTO article_tags (article_id, tag_id) VALUES (:article_id, :tag_id)";
            $stmt = $this->conn->prepare($insertQuery);
            foreach ($tag_ids as $tag_id) {
                $stmt->bindParam(':article_id', $article_id);
                $stmt->bindParam(':tag_id', $tag_id);
                $stmt->execute();
            }
        }
        return true;
    }

    // Récupérer les IDs des tags d'un article
    public function getArticleTagIds($article_id) {
        $query = "SELECT tag_id FROM article_tags WHERE article_id = :article_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>
