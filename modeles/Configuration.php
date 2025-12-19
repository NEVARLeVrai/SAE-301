<?php
/**
 * ===========================================
 * Modèle Configuration - Paramètres du site
 * ===========================================
 * 
 * User Stories concernées :
 * - US-09 : Gestion des configurations globales
 * - US-38 : Configuration des moyens de paiement
 * 
 * Paramètres : titre site, mode maintenance, clés API paiement...
 */
/**
 * Class Configuration
 *
 * Accès et modification des paramètres globaux du site (clé/valeur).
 * Utile pour stocker les réglages administrables (modes, clés API, options paiement).
 *
 * @package Modeles
 */
class Configuration {
    private $conn;
    private $table_name = "configurations";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Récupère toutes les configurations sous forme clé => valeur.
     *
     * @return array
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    /**
     * Met à jour une configuration existante.
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function update($key, $value) {
        $query = "UPDATE " . $this->table_name . " SET setting_value = :value WHERE setting_key = :key";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':key', $key);
        
        return $stmt->execute();
    }

    /**
     * Récupère la valeur d'une configuration.
     *
     * @param string $key
     * @return string|null
     */
    public function get($key) {
        $query = "SELECT setting_value FROM " . $this->table_name . " WHERE setting_key = :key LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['setting_value'] : null;
    }
}
?>
