<?php
/**
 * ===========================================
 * Modèle Produit - Gestion de la boutique
 * ===========================================
 * 
 * User Stories concernées :
 * - US-03 : Achat de partitions et instruments
 * - US-04 : Vente d'instruments d'occasion (status pending)
 * - US-05 : Vente de compositions (Musicien Pro)
 * - US-10 : Modération des annonces
 * - US-32 : Téléchargement produit numérique
 * - US-34 : Gestion produits Musicien (soft delete)
 * 
 * Types : partition_virtuelle, partition_physique, instrument
 * Status : pending, approved, rejected
 */
class Produit {
    private $conn;
    private $table_name = "products";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lire tous les produits (pour admin) - exclut les supprimés
    public function getProducts() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE is_deleted = 0 ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lire les produits approuvés uniquement (pour boutique visiteur)
    public function getApprovedProducts() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'approved' AND is_deleted = 0 ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lire les produits par statut (ex: pending) - pour modération
    public function getProductsByStatus($status) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = :status AND is_deleted = 0 ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lire un produit par ID
    public function getProductById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer un produit
    public function create($name, $description, $price, $stock, $category, $type, $image_url, $file_url, $seller_id, $status = 'approved') {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, description, price, stock, category, type, image_url, file_url, seller_id, status, created_at) 
                  VALUES (:name, :description, :price, :stock, :category, :type, :image_url, :file_url, :seller_id, :status, NOW())";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':file_url', $file_url);
        $stmt->bindParam(':seller_id', $seller_id);
        $stmt->bindParam(':status', $status);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Mettre à jour un produit
    public function update($id, $name, $description, $price, $stock, $category, $type, $image_url, $file_url, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, description = :description, price = :price, stock = :stock, 
                      category = :category, type = :type, image_url = :image_url, file_url = :file_url, status = :status
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':file_url', $file_url);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Supprimer un produit (Suppression logique)
    public function delete($id) {
        $query = "UPDATE " . $this->table_name . " SET is_deleted = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Compter les produits
    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Créer un produit depuis les données POST (encapsulation)
    public function createFromPost($seller_id, $uploadedImage = null, $uploadedFile = null) {
        $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
        $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
        $category = isset($_POST['category']) ? htmlspecialchars($_POST['category']) : '';
        $type = isset($_POST['type']) ? $_POST['type'] : '';
        $status = isset($_POST['status']) ? $_POST['status'] : 'approved';
        $image_url = $uploadedImage ?? '';
        $file_url = $uploadedFile ?? '';

        return $this->create($name, $description, $price, $stock, $category, $type, $image_url, $file_url, $seller_id, $status);
    }

    // Mettre à jour un produit depuis les données POST (encapsulation)
    public function updateFromPost($uploadedImage = null, $uploadedFile = null) {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
        $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
        $category = isset($_POST['category']) ? htmlspecialchars($_POST['category']) : '';
        $type = isset($_POST['type']) ? $_POST['type'] : '';
        $status = isset($_POST['status']) ? $_POST['status'] : 'approved';
        $image_url = $uploadedImage ?? (isset($_POST['current_image']) ? $_POST['current_image'] : '');
        $file_url = $uploadedFile ?? (isset($_POST['current_file']) ? $_POST['current_file'] : '');

        return $this->update($id, $name, $description, $price, $stock, $category, $type, $image_url, $file_url, $status);
    }

    // Récupérer l'ID depuis POST
    public function getIdFromPost() {
        return isset($_POST['id']) ? intval($_POST['id']) : 0;
    }

    // Créer une annonce instrument d'occasion depuis POST (US-04 - encapsulation)
    public function createInstrumentFromPost($seller_id, $uploadedImage = null) {
        $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
        $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        $category = isset($_POST['category']) ? htmlspecialchars($_POST['category']) : '';
        $type = 'instrument';
        $status = 'pending'; // Modération obligatoire
        $stock = 1; // Pièce unique
        $image_url = $uploadedImage ?? '';

        return $this->create($name, $description, $price, $stock, $category, $type, $image_url, null, $seller_id, $status);
    }

    // Récupérer le nom et prix depuis POST (pour notifications)
    public function getNameFromPost() {
        return isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
    }

    public function getPriceFromPost() {
        return isset($_POST['price']) ? floatval($_POST['price']) : 0;
    }
}
?>