<?php
/**
 * ===========================================
 * Modèle Database - Connexion à la BDD
 * ===========================================
 * 
 * Singleton pattern pour une connexion unique.
 * Utilise PDO pour des requêtes sécurisées.
 * 
 * Configuration à adapter selon l'environnement :
 * - Local : root/root
 * - Production : identifiants sécurisés
 */
class Database {
    private $host = "localhost";
    private $db_name = "sae301_musique_SOARES_Daniels"; 
    private $username = "root";
    private $password = "root"; 
    public $conn;

    /**
     * Retourne une connexion PDO à la base de données
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Erreur de connexion : " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
