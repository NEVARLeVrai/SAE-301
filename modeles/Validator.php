<?php
/**
 * ===========================================
 * Classe Validator - Validation des données
 * ===========================================
 * 
 * Centralise toutes les validations pour éviter la duplication
 * et assurer la cohérence dans toute l'application
 */
/**
 * Class Validator
 *
 * Centralise les règles de validation côté serveur pour les formulaires.
 * Fournit des helpers pour valider email, username, password, et opérations liées.
 *
 * @package Modeles
 */
class Validator {
    /** @var array Liste des messages d'erreur collectés */
    private $errors = [];
    
    /**
     * Valide une adresse email.
     *
     * @param string $email
     * @param string $fieldName Nom du champ (info pour messages)
     * @param bool $required
     * @return bool
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
     * Valide la longueur et l'existence du mot de passe.
     *
     * @param string $password
     * @param string $fieldName
     * @param int $minLength
     * @param bool $required
     * @return bool
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
     * Valide un nom d'utilisateur (longueur, caractères autorisés).
     *
     * @param string $username
     * @param string $fieldName
     * @param bool $required
     * @return bool
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
     * Valide les données de connexion (email + mot de passe).
     *
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function validateLogin($email, $password) {
        $this->errors = [];
        $isValid = true;
        
        $isValid = $this->validateEmail($email, 'email', true) && $isValid;
        $isValid = $this->validatePassword($password, 'password', 3, true) && $isValid;
        
        return $isValid;
    }
    
    /**
     * Valide les données d'inscription (username, email, password).
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @return bool
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
     * Vérifie l'existence d'un email en base.
     *
     * @param PDO $db
     * @param string $email
     * @param int|null $excludeUserId
     * @return bool
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
     * Vérifie l'existence d'un nom d'utilisateur en base.
     *
     * @param PDO $db
     * @param string $username
     * @param int|null $excludeUserId
     * @return bool
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
     * Retourne la liste des erreurs collectées.
     *
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Retourne les erreurs sous forme de chaîne concaténée.
     *
     * @return string
     */
    public function getErrorsString() {
        return implode(' ', $this->errors);
    }
    
    /**
     * Indique si des erreurs ont été collectées.
     *
     * @return bool
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Réinitialise la liste des erreurs.
     *
     * @return void
     */
    public function reset() {
        $this->errors = [];
    }
}
?>

