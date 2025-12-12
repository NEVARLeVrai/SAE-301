<?php
/**
 * ===========================================
 * Classe Validator - Validation des données
 * ===========================================
 * 
 * Centralise toutes les validations pour éviter la duplication
 * et assurer la cohérence dans toute l'application
 */
class Validator {
    private $errors = [];
    
    /**
     * Valider un email
     */
    public function validateEmail($email, $fieldName = 'email', $required = true) {
        $email = trim($email);
        
        if ($required && empty($email)) {
            $this->errors[] = "L'email est requis.";
            return false;
        }
        
        if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = "Format d'email invalide.";
                return false;
            }
            
            if (strlen($email) > 255) {
                $this->errors[] = "L'email ne doit pas dépasser 255 caractères.";
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Valider un mot de passe
     */
    public function validatePassword($password, $fieldName = 'password', $minLength = 6, $required = true) {
        if ($required && empty($password)) {
            $this->errors[] = "Le mot de passe est requis.";
            return false;
        }
        
        if (!empty($password)) {
            if (strlen($password) < $minLength) {
                $this->errors[] = "Le mot de passe doit contenir au moins {$minLength} caractères.";
                return false;
            }
            
            if (strlen($password) > 72) {
                $this->errors[] = "Le mot de passe ne doit pas dépasser 72 caractères.";
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Valider un nom d'utilisateur
     */
    public function validateUsername($username, $fieldName = 'username', $required = true) {
        $username = trim($username);
        
        if ($required && empty($username)) {
            $this->errors[] = "Le nom d'utilisateur est requis.";
            return false;
        }
        
        if (!empty($username)) {
            if (strlen($username) < 3) {
                $this->errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères.";
                return false;
            }
            
            if (strlen($username) > 50) {
                $this->errors[] = "Le nom d'utilisateur ne doit pas dépasser 50 caractères.";
                return false;
            }
            
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $this->errors[] = "Le nom d'utilisateur ne peut contenir que des lettres, chiffres et underscores.";
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Valider les données de login
     */
    public function validateLogin($email, $password) {
        $this->errors = [];
        $isValid = true;
        
        $isValid = $this->validateEmail($email, 'email', true) && $isValid;
        $isValid = $this->validatePassword($password, 'password', 3, true) && $isValid;
        
        return $isValid;
    }
    
    /**
     * Valider les données d'inscription
     */
    public function validateRegister($username, $email, $password) {
        $this->errors = [];
        $isValid = true;
        
        $isValid = $this->validateUsername($username, 'username', true) && $isValid;
        $isValid = $this->validateEmail($email, 'email', true) && $isValid;
        $isValid = $this->validatePassword($password, 'password', 6, true) && $isValid;
        
        return $isValid;
    }
    
    /**
     * Vérifier si un email existe déjà dans la base de données
     */
    public function emailExists($db, $email, $excludeUserId = null) {
        $query = "SELECT id FROM users WHERE email = :email";
        if ($excludeUserId !== null) {
            $query .= " AND id != :exclude_id";
        }
        $query .= " LIMIT 1";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        if ($excludeUserId !== null) {
            $stmt->bindParam(':exclude_id', $excludeUserId);
        }
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Vérifier si un username existe déjà dans la base de données
     */
    public function usernameExists($db, $username, $excludeUserId = null) {
        $query = "SELECT id FROM users WHERE username = :username";
        if ($excludeUserId !== null) {
            $query .= " AND id != :exclude_id";
        }
        $query .= " LIMIT 1";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        if ($excludeUserId !== null) {
            $stmt->bindParam(':exclude_id', $excludeUserId);
        }
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Récupérer les erreurs
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Récupérer les erreurs sous forme de chaîne
     */
    public function getErrorsString() {
        return implode(' ', $this->errors);
    }
    
    /**
     * Vérifier s'il y a des erreurs
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Réinitialiser les erreurs
     */
    public function reset() {
        $this->errors = [];
    }
}
?>

