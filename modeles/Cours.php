<?php
/**
 * ===========================================
 * Modèle Cours - Gestion des cours de musique
 * ===========================================
 * 
 * User Stories concernées :
 * - US-01 : Recherche et consultation des cours
 * - US-12 : Filtrage avancé (niveau, instrument, catégorie)
 * - US-15 : Visualisation détaillée d'un cours
 * - US-31 : Consultation des cours avec ressources
 * 
 * Niveaux : debutant, intermediaire, avance
 */
class Cours {
    private $conn;
    private $table_name = "courses";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lire les cours avec filtres optionnels
    public function getCourses($filters = []) {
        $query = "SELECT c.id, c.title, c.description, c.level, c.instrument, c.category, u.username as author 
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u ON c.author_id = u.id
                  WHERE 1=1";

        $params = [];

        if (!empty($filters['level'])) {
            $query .= " AND c.level = :level";
            $params[':level'] = $filters['level'];
        }

        if (!empty($filters['instrument'])) {
            $query .= " AND c.instrument = :instrument";
            $params[':instrument'] = $filters['instrument'];
        }

        if (!empty($filters['category'])) {
            $query .= " AND c.category = :category";
            $params[':category'] = $filters['category'];
        }

        $query .= " ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lire un cours par son ID
    public function getCourseById($id) {
        $query = "SELECT c.*, u.username as author 
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u ON c.author_id = u.id
                  WHERE c.id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Récupérer les listes distinctes pour les filtres
    public function getDistinctValues($column) {
        $query = "SELECT DISTINCT " . $column . " FROM " . $this->table_name . " ORDER BY " . $column;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Récupérer les derniers cours (pour l'accueil)
    public function getLatestCourses($limit = 3) {
        $query = "SELECT c.id, c.title, c.description, c.level, c.instrument, c.category, u.username as author 
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u ON c.author_id = u.id
                  ORDER BY c.created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Compter les cours
    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Créer un cours
    public function create($title, $description, $content, $author_id, $level, $instrument, $category) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (title, description, content, author_id, level, instrument, category, created_at) 
                  VALUES (:title, :description, :content, :author_id, :level, :instrument, :category, NOW())";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':author_id', $author_id);
        $stmt->bindParam(':level', $level);
        $stmt->bindParam(':instrument', $instrument);
        $stmt->bindParam(':category', $category);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Mettre à jour un cours
    public function update($id, $title, $description, $content, $level, $instrument, $category) {
        $query = "UPDATE " . $this->table_name . " 
                  SET title = :title, description = :description, content = :content,
                      level = :level, instrument = :instrument, category = :category
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':level', $level);
        $stmt->bindParam(':instrument', $instrument);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Supprimer un cours
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Créer un cours depuis les données POST (encapsulation)
    public function createFromPost($author_id) {
        $title = isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '';
        $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
        $content = isset($_POST['content']) ? $_POST['content'] : '';
        $level = isset($_POST['level']) ? $_POST['level'] : '';
        $instrument = isset($_POST['instrument']) ? htmlspecialchars($_POST['instrument']) : '';
        $category = isset($_POST['category']) ? htmlspecialchars($_POST['category']) : '';

        return $this->create($title, $description, $content, $author_id, $level, $instrument, $category);
    }

    // Mettre à jour un cours depuis les données POST (encapsulation)
    public function updateFromPost() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $title = isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '';
        $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
        $content = isset($_POST['content']) ? $_POST['content'] : '';
        $level = isset($_POST['level']) ? $_POST['level'] : '';
        $instrument = isset($_POST['instrument']) ? htmlspecialchars($_POST['instrument']) : '';
        $category = isset($_POST['category']) ? htmlspecialchars($_POST['category']) : '';

        return $this->update($id, $title, $description, $content, $level, $instrument, $category);
    }

    // Récupérer l'ID depuis POST
    public function getIdFromPost() {
        return isset($_POST['id']) ? intval($_POST['id']) : 0;
    }
}
?>
