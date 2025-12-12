<?php
/**
 * ===========================================
 * Modèle User - Gestion des utilisateurs
 * ===========================================
 * 
 * User Stories concernées :
 * - US-26 : CRUD utilisateurs (Admin)
 * - Authentification pour tous les rôles
 * 
 * Rôles disponibles : admin, redacteur, musicien, visiteur
 */
class User {
    private $conn;
    private $table_name = "users";

    // Attributs privés (encapsulation)
    private $id;
    private $username;
    private $email;
    private $password;
    private $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Getters publics pour accéder aux attributs
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getRole() { return $this->role; }

    // Vérifier les identifiants pour le login
    public function login($email, $password) {
        $query = "SELECT id, username, password, role FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }

    // Récupérer un utilisateur par ID
    public function getUserById($id) {
        $query = "SELECT id, username, email, role FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer un nouvel utilisateur
    public function create($username, $email, $password, $role = 'visiteur') {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, email, password, role, created_at) 
                  VALUES (:username, :email, :password, :role, NOW())";

        $stmt = $this->conn->prepare($query);

        // Hashage du mot de passe
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // --- AJOUTS POUR US-26 (Gestion des utilisateurs) ---

    // Récupérer tous les utilisateurs
    public function getAllUsers() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mettre à jour un utilisateur (Rôle, Email, Username)
    public function update($id, $username, $email, $role) {
        $query = "UPDATE " . $this->table_name . " 
                  SET username = :username, email = :email, role = :role 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Supprimer un utilisateur
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Créer un utilisateur depuis les données POST (encapsulation)
    public function createFromPost() {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role = isset($_POST['role']) ? $_POST['role'] : 'visiteur';

        // Utilisation de la classe Validator
        $validator = new Validator();
        
        // Validation des données
        if (!$validator->validateRegister($username, $email, $password)) {
            $_SESSION['validation_errors'] = $validator->getErrors();
            return false;
        }
        
        // Vérifier si l'email existe déjà
        if ($validator->emailExists($this->conn, $email)) {
            $_SESSION['validation_errors'] = ["Cet email est déjà utilisé."];
            return false;
        }
        
        // Vérifier si le username existe déjà
        if ($validator->usernameExists($this->conn, $username)) {
            $_SESSION['validation_errors'] = ["Ce nom d'utilisateur est déjà utilisé."];
            return false;
        }

        $username = htmlspecialchars($username);
        $email = htmlspecialchars($email);
        return $this->create($username, $email, $password, $role);
    }

    // Mettre à jour un utilisateur depuis les données POST (encapsulation)
    public function updateFromPost() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
        $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
        $role = isset($_POST['role']) ? $_POST['role'] : 'visiteur';

        return $this->update($id, $username, $email, $role);
    }

    // Login depuis les données POST (encapsulation)
    public function loginFromPost() {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        // Utilisation de la classe Validator
        $validator = new Validator();
        
        // Validation des données
        if (!$validator->validateLogin($email, $password)) {
            $_SESSION['validation_errors'] = $validator->getErrors();
            return false;
        }

        return $this->login($email, $password);
    }

    // Récupérer l'ID depuis POST
    public function getIdFromPost() {
        return isset($_POST['id']) ? intval($_POST['id']) : 0;
    }
}
?>
