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
/**
 * Class Database
 *
 * Fournit une connexion PDO via le pattern singleton (méthode `getConnection`).
 * Attention: les identifiants sont codés en dur ici pour l'exemple ; en production
 * utilisez des variables d'environnement ou un fichier de configuration exclu du VCS.
 *
 * @package Modeles
 */
class Database {
    private $host = "localhost";
    private $db_name = "soares_sae301"; 
    private $username = "root";
    private $password = ""; 
    public $conn;

    /**
     * Retourne une connexion PDO à la base de données.
     *
     * @return PDO|null
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
