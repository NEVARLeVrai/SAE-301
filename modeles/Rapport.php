<?php
/**
 * ===========================================
 * Modèle Rapport - Rapports et statistiques
 * ===========================================
 * 
 * User Stories concernées :
 * - US-27 : Rapports statistiques + Export CSV/PDF
 * 
 * Fonctionnalités :
 * - Statistiques des commandes par période
 * - Ventes par catégorie
 * - Export CSV et génération PDF
 * 
 * Encapsule toutes les requêtes SQL liées aux rapports.
 */
class Rapport {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Récupérer les statistiques des commandes sur une période
     */
    public function getOrderStats($date_start, $date_end) {
        $query = "SELECT COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as total_revenue 
                  FROM orders WHERE DATE(created_at) BETWEEN :start AND :end AND status = 'paid'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $date_start);
        $stmt->bindParam(':end', $date_end);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer le nombre de nouveaux utilisateurs sur une période
     */
    public function getNewUsersCount($date_start, $date_end) {
        $query = "SELECT COUNT(*) as new_users FROM users WHERE DATE(created_at) BETWEEN :start AND :end";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $date_start);
        $stmt->bindParam(':end', $date_end);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['new_users'];
    }

    /**
     * Récupérer le nombre de nouveaux articles sur une période
     */
    public function getNewArticlesCount($date_start, $date_end) {
        $query = "SELECT COUNT(*) as new_articles FROM articles WHERE DATE(created_at) BETWEEN :start AND :end AND is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $date_start);
        $stmt->bindParam(':end', $date_end);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['new_articles'];
    }

    /**
     * Récupérer les ventes par catégorie sur une période
     */
    public function getSalesByCategory($date_start, $date_end) {
        $query = "SELECT p.category, SUM(oi.price * oi.quantity) as total
                  FROM order_items oi
                  JOIN products p ON oi.product_id = p.id
                  JOIN orders o ON oi.order_id = o.id
                  WHERE DATE(o.created_at) BETWEEN :start AND :end AND o.status = 'paid'
                  GROUP BY p.category
                  ORDER BY total DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $date_start);
        $stmt->bindParam(':end', $date_end);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les dernières commandes sur une période
     */
    public function getRecentOrders($date_start, $date_end, $limit = 10) {
        $query = "SELECT o.*, u.username FROM orders o 
                  LEFT JOIN users u ON o.user_id = u.id
                  WHERE DATE(o.created_at) BETWEEN :start AND :end
                  ORDER BY o.created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $date_start);
        $stmt->bindParam(':end', $date_end);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer toutes les statistiques pour la page de rapports
     */
    public function getAllStats($date_start, $date_end) {
        $orderStats = $this->getOrderStats($date_start, $date_end);
        
        return [
            'total_orders' => $orderStats['total_orders'],
            'total_revenue' => $orderStats['total_revenue'],
            'new_users' => $this->getNewUsersCount($date_start, $date_end),
            'new_articles' => $this->getNewArticlesCount($date_start, $date_end)
        ];
    }

    /**
     * Récupérer les données d'export des commandes
     */
    public function getOrdersForExport($date_start, $date_end) {
        $query = "SELECT o.id, u.username, u.email, o.total_amount, o.status, o.created_at
                  FROM orders o
                  LEFT JOIN users u ON o.user_id = u.id
                  WHERE DATE(o.created_at) BETWEEN :start AND :end
                  ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $date_start);
        $stmt->bindParam(':end', $date_end);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les données d'export des utilisateurs
     */
    public function getUsersForExport($date_start, $date_end) {
        $query = "SELECT id, username, email, role, created_at FROM users
                  WHERE DATE(created_at) BETWEEN :start AND :end
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $date_start);
        $stmt->bindParam(':end', $date_end);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les données d'export des produits
     */
    public function getProductsForExport() {
        $query = "SELECT id, name, price, stock, category, type, status FROM products
                  WHERE is_deleted = 0
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les commandes détaillées pour le PDF
     */
    public function getOrdersDetailForPdf($date_start, $date_end) {
        $query = "SELECT o.id, u.username, o.total_amount, o.status, o.created_at
                  FROM orders o LEFT JOIN users u ON o.user_id = u.id
                  WHERE DATE(o.created_at) BETWEEN :start AND :end 
                  ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $date_start);
        $stmt->bindParam(':end', $date_end);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
