<?php
/**
 * Class Article
 *
 * Gestion des articles du blog : lecture, création, mise à jour, suppression logique,
 * recherche et publication différée.
 *
 * Important: les méthodes `createFromPost` et `updateFromPost` lisent `$_POST` —
 * elles encapsulent la construction des paramètres mais modifient l'état global.
 *
 * @package Modeles
 */
class Article {
    private $conn;
    private $table_name = "articles";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Lire les articles avec filtre par catégorie et statut.
     *
     * @param string|null $category
     * @param string $status 'published'|'all'|...'status'
     * @return array
     */
    public function getArticles($category = null, $status = 'published') {
        $query = "SELECT a.*, u.username as author 
                  FROM " . $this->table_name . " a
                  LEFT JOIN users u ON a.author_id = u.id
                  WHERE is_deleted = 0";
        
        if ($status !== 'all') {
            $query .= " AND status = :status";
            // US-22 : Vérifier la date de publication
            if ($status === 'published') {
                $query .= " AND (published_at IS NULL OR published_at <= NOW())";
            }
        }

        if ($category) {
            $query .= " AND category = :category";
        }

        $query .= " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);

        if ($status !== 'all') {
            $stmt->bindParam(':status', $status);
        }

        if ($category) {
            $stmt->bindParam(':category', $category);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un article par son identifiant.
     *
     * @param int $id
     * @return array|false
     */
    public function getArticleById($id) {
        $query = "SELECT a.*, u.username as author 
                  FROM " . $this->table_name . " a
                  LEFT JOIN users u ON a.author_id = u.id
                  WHERE a.id = :id AND is_deleted = 0
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche d'articles par terme dans le titre ou le contenu.
     *
     * @param string $term
     * @return array
     */
    public function searchArticles($term) {
        $query = "SELECT a.*, u.username as author 
                  FROM " . $this->table_name . " a
                  LEFT JOIN users u ON a.author_id = u.id
                  WHERE (title LIKE :term OR content LIKE :term) AND is_deleted = 0
                  AND status = 'published' AND (published_at IS NULL OR published_at <= NOW())
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $term = "%" . $term . "%";
        $stmt->bindParam(':term', $term);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les derniers articles publiés.
     *
     * @param int $limit
     * @return array
     */
    public function getLatestArticles($limit = 3) {
        $query = "SELECT a.*, u.username as author 
                  FROM " . $this->table_name . " a
                  LEFT JOIN users u ON a.author_id = u.id
                  WHERE status = 'published' AND is_deleted = 0
                  AND (published_at IS NULL OR published_at <= NOW())
                  ORDER BY created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère la liste des catégories distinctes.
     *
     * @return array
     */
    public function getCategories() {
        $query = "SELECT DISTINCT category FROM " . $this->table_name . " WHERE is_deleted = 0 ORDER BY category";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Crée un nouvel article en base.
     *
     * @param string $title
     * @param string $content
     * @param int $author_id
     * @param string $category
     * @param string $image_url
     * @param string $status
     * @param string|null $published_at
     * @return bool
     */
    public function create($title, $content, $author_id, $category, $image_url, $status = 'published', $published_at = null) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (title, content, author_id, category, image_url, status, published_at, created_at) 
                  VALUES (:title, :content, :author_id, :category, :image_url, :status, :published_at, NOW())";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':author_id', $author_id);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':published_at', $published_at);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Met à jour un article existant.
     *
     * @param int $id
     * @param string $title
     * @param string $content
     * @param string $category
     * @param string $image_url
     * @param string $status
     * @param string|null $published_at
     * @return bool
     */
    public function update($id, $title, $content, $category, $image_url, $status, $published_at = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET title = :title, content = :content, category = :category, 
                      image_url = :image_url, status = :status, published_at = :published_at, updated_at = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':published_at', $published_at);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Suppression logique d'un article (flag `is_deleted`).
     *
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $query = "UPDATE " . $this->table_name . " SET is_deleted = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Récupère les articles d'un auteur donné.
     *
     * @param int $author_id
     * @return array
     */
    public function getArticlesByAuthor($author_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE author_id = :author_id AND is_deleted = 0 ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':author_id', $author_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compte le nombre d'articles non supprimés.
     *
     * @return int
     */
    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    /**
     * Crée un article à partir des données `$_POST` (encapsulation).
     *
     * @param int $author_id
     * @param string|null $uploadedImage
     * @return bool
     */
    public function createFromPost($author_id, $uploadedImage = null) {
        $title = isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '';
        $content = isset($_POST['content']) ? $_POST['content'] : '';
        $category = isset($_POST['category']) ? $_POST['category'] : '';
        $status = isset($_POST['status']) ? $_POST['status'] : 'published';
        $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : null;
        $image_url = $uploadedImage ?? '';

        return $this->create($title, $content, $author_id, $category, $image_url, $status, $published_at);
    }

    /**
     * Met à jour un article depuis les données `$_POST`.
     *
     * @param string|null $uploadedImage
     * @return bool
     */
    public function updateFromPost($uploadedImage = null) {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $title = isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '';
        $content = isset($_POST['content']) ? $_POST['content'] : '';
        $category = isset($_POST['category']) ? $_POST['category'] : '';
        $status = isset($_POST['status']) ? $_POST['status'] : 'published';
        $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : null;
        $image_url = $uploadedImage ?? (isset($_POST['current_image']) ? $_POST['current_image'] : '');

        return $this->update($id, $title, $content, $category, $image_url, $status, $published_at);
    }

    /**
     * Récupère l'ID envoyé en POST.
     *
     * @return int
     */
    public function getIdFromPost() {
        return isset($_POST['id']) ? intval($_POST['id']) : 0;
    }
}
?>