<?php
/**
 * Class FileUpload
 *
 * Centralise la logique d'upload des fichiers et images utilisés par l'application.
 * - Limite la taille à 5Mo
 * - Vérifie le MIME via `finfo`
 * - Déplace les fichiers vers les répertoires configurés
 *
 * Security notes:
 * - Il est recommandé d'effectuer une validation additionnelle (ex: getimagesize
 *   et ré-encodage pour les images) et de stocker les fichiers sensibles hors du webroot.
 *
 * @package Modeles
 */
class FileUpload {
    // Constantes pour les chemins (à adapter selon votre structure)
    const UPLOAD_DIR_ASSETS = "../../assets/";
    const UPLOAD_DIR_IMG = "../../img/";
    const UPLOAD_DIR_UPLOADS = "../../uploads/";
    
    // Types de fichiers autorisés
    const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    const ALLOWED_FILE_TYPES = ['application/pdf', 'audio/mpeg', 'audio/wav'];
    
    // Taille maximale (5 Mo)
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 Mo en octets

    /**
     * Upload d'une image
     * @param string $fieldName Nom du champ dans $_FILES
     * @param string $targetDir Dossier de destination (utiliser les constantes)
     * @return string|null Nom du fichier uploadé ou null en cas d'erreur
     */
    public static function uploadImage($fieldName = 'image', $targetDir = self::UPLOAD_DIR_ASSETS) {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] != 0) {
            return null;
        }

        $file = $_FILES[$fieldName];
        
        // Vérifier le type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, self::ALLOWED_IMAGE_TYPES)) {
            return null;
        }
        
        // Vérifier la taille
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return null;
        }
        
        // Générer un nom unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = time() . "_" . uniqid() . "." . $extension;
        $target_file = $targetDir . $file_name;
        
        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return $file_name;
        }
        
        return null;
    }

    /**
     * Upload d'un fichier (PDF, audio, etc.)
     * @param string $fieldName Nom du champ dans $_FILES
     * @param string $targetDir Dossier de destination
     * @return string|null Nom du fichier uploadé ou null en cas d'erreur
     */
    public static function uploadFile($fieldName = 'file', $targetDir = self::UPLOAD_DIR_ASSETS) {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] != 0) {
            return null;
        }

        $file = $_FILES[$fieldName];
        
        // Vérifier le type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, self::ALLOWED_FILE_TYPES)) {
            return null;
        }
        
        // Vérifier la taille
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return null;
        }
        
        // Générer un nom unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = time() . "_" . uniqid() . "." . $extension;
        $target_file = $targetDir . $file_name;
        
        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return $file_name;
        }
        
        return null;
    }

    /**
     * Supprimer un fichier (cherche dans plusieurs dossiers possibles)
     * @param string $fileName Nom du fichier à supprimer
     * @return bool True si supprimé, false sinon
     */
    public static function deleteFile($fileName) {
        if (empty($fileName)) {
            return false;
        }
        
        $possiblePaths = [
            self::UPLOAD_DIR_ASSETS,
            self::UPLOAD_DIR_IMG,
            self::UPLOAD_DIR_UPLOADS
        ];
        
        foreach ($possiblePaths as $path) {
            $filePath = $path . $fileName;
            if (file_exists($filePath) && is_file($filePath)) {
                return @unlink($filePath);
            }
        }
        
        return false;
    }
}
?>

