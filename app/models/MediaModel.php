<?php

namespace App\Models;

use App\Helpers\LogHelper;
use App\Core\Model;

class MediaModel extends Model
{
    private $uploadPath = ROOT_PATH . '/public/uploads/media/';
    private $allowedTypes = [
        // 'svg' intentionally excluded: SVG can embed scripts (stored XSS) when
        // served inline from public/uploads
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'],
        'video' => ['mp4', 'webm', 'ogv'],
        'audio' => ['mp3', 'wav', 'ogg']
    ];
    private $maxFileSize = 52428800; // 50MB

    public function __construct()
    {
        parent::__construct();
        $this->table = 'media';
    }

    /**
     * Restituisce un messaggio di errore leggibile in base al codice di errore di upload
     * @param int $errorCode Codice di errore PHP UPLOAD_ERR_*
     * @return string Messaggio di errore descrittivo
     */
    public function getUploadError($errorCode)
    {
        $errors = [
            UPLOAD_ERR_OK         => 'Nessun errore, file caricato con successo',
            UPLOAD_ERR_INI_SIZE   => 'Il file caricato supera la dimensione massima consentita dal server',
            UPLOAD_ERR_FORM_SIZE  => 'Il file caricato supera la dimensione massima specificata nel form',
            UPLOAD_ERR_PARTIAL    => 'Il file è stato caricato solo parzialmente',
            UPLOAD_ERR_NO_FILE    => 'Nessun file è stato caricato',
            UPLOAD_ERR_NO_TMP_DIR => 'Cartella temporanea mancante',
            UPLOAD_ERR_CANT_WRITE => 'Impossibile scrivere il file su disco',
            UPLOAD_ERR_EXTENSION  => 'Un\'estensione PHP ha interrotto il caricamento del file'
        ];

        return $errors[$errorCode] ?? 'Errore sconosciuto durante il caricamento del file';
    }

    /**
     * Reformat $_FILES array to a uniform array of files
     * @param array $file Input file array (single or multiple)
     * @return array Array of files with keys: name, type, tmp_name, error, size
     */
    private function reformatFilesArray($file)
    {
        $result = [];
        if (is_array($file['name'])) {
            // Multiple files
            $fileCount = count($file['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                $result[] = [
                    'name' => $file['name'][$i],
                    'type' => $file['type'][$i],
                    'tmp_name' => $file['tmp_name'][$i],
                    'error' => $file['error'][$i],
                    'size' => $file['size'][$i]
                ];
            }
        } else {
            // Single file
            $result[] = [
                'name' => $file['name'],
                'type' => $file['type'],
                'tmp_name' => $file['tmp_name'],
                'error' => $file['error'],
                'size' => $file['size']
            ];
        }
        return $result;
    }

    /**
     * Validate uploaded file (type, extension, size)
     * @param array $file File array
     * @return array File info: name, type, extension
     * @throws \Exception If file is not valid
     */
    private function validateFile($file)
    {
        // Get extension (lowercase, no dot)
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = $this->allowedTypes;
        $type = null;
        foreach ($allowedTypes as $category => $extList) {
            if (in_array($extension, $extList)) {
                $type = $category;
                break;
            }
        }
        if (!$type) {
            throw new \Exception('File type not allowed: ' . $extension);
        }
        if ($file['size'] > $this->maxFileSize) {
            throw new \Exception('File size exceeds the allowed limit');
        }
        return [
            'name' => $file['name'],
            'type' => $type,
            'extension' => $extension
        ];
    }

    /**
     * Carica uno o più file sul server
     * @param array $files Array di file da caricare
     * @param int $userId ID dell'utente che carica i file
     * @param array $extraData Dati aggiuntivi per i file
     * @return array Array di oggetti media caricati
     * @throws \Exception Se si verifica un errore durante il caricamento
     */
    public function upload($files, $userId, $extraData = [])
    {

        $uploaded = [];

        // Verifica se è stato effettivamente caricato un file
        if (empty($files) || (is_array($files) && empty($files['name']))) {
            throw new \Exception('Nessun file selezionato per il caricamento');
        }

        // Uniforma sempre la struttura dei file (anche per singolo file)
        $files = $this->reformatFilesArray($files);

        foreach ($files as $file) {
            // Controllo struttura array file

            $requiredKeys = ['name','type','tmp_name','error','size'];
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $file)) {
                    LogHelper::error('Upload file: struttura file non valida. Chiave mancante: ' . $key);
                    continue 2; // Salta questo file
                }
            }
            // Verifica se c'è stato un errore nel caricamento
            if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
                continue; // Salta se nessun file è stato caricato
            }

            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('Errore nel caricamento del file: ' . $this->getUploadError($file['error']));
            }

            $fileInfo = $this->validateFile($file);
            $filename = $this->generateUniqueFilename($fileInfo['name']);
            // Ensure leading slash in relativePath (filepath)
            $relativePath = '/' . date('Y/m/');
            if ($relativePath[0] !== '/') {
                $relativePath = '/' . $relativePath;
            }
            $fullPath = $this->uploadPath . $relativePath;

            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            if (!move_uploaded_file($file['tmp_name'], $fullPath . $filename)) {
                throw new \Exception('Impossibile spostare il file caricato');
            }

            // Genera thumbnail per le immagini
            $dimensions = null;
            if ($fileInfo['type'] === 'image') {
                $dimensions = $this->generateThumbnail($fullPath . $filename, $filename, $relativePath);
            }

            // Salva nel database
            $mediaId = $this->insert([
                'user_id' => $userId,
                'title' => $extraData['title'] ?? pathinfo($file['name'], PATHINFO_FILENAME),
                'description' => $extraData['description'] ?? '',
                'alt_text' => $extraData['alt_text'] ?? '',
                'filename' => $filename,
                'filepath' => $relativePath,
                'filetype' => $fileInfo['type'] . '/' . $fileInfo['extension'],
                'filesize' => $file['size'],
                'width' => $dimensions['width'] ?? null,
                'height' => $dimensions['height'] ?? null
            ]);

            $uploaded[] = $this->getById($mediaId);
        }

        return $uploaded;
    }

    /**
     * Generate a thumbnail for an image file (jpg, png, gif, webp)
     * @param string $filePath Absolute path to the original image
     * @param string $filename Name of the original file
     * @param string $relativePath Relative path (e.g. '2025/05/')
     * @param int $thumbWidth Width of the thumbnail (default 300px)
     * @return array|null Thumbnail dimensions [width, height, path] or null on failure
     */
    private function generateThumbnail($filePath, $filename, $relativePath, $thumbWidth = 300)
    {
        $thumbDir = $this->uploadPath . $relativePath . 'thumbs/';
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }
        $thumbPath = $thumbDir . $filename;
        $info = getimagesize($filePath);
        if (!$info) {
            return null;
        }
        list($width, $height, $type) = $info;
        $ratio = $width / $height;
        $newWidth = $thumbWidth;
        $newHeight = intval($thumbWidth / $ratio);

        switch ($type) {
            case IMAGETYPE_JPEG:
                $srcImg = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $srcImg = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_GIF:
                $srcImg = imagecreatefromgif($filePath);
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    $srcImg = imagecreatefromwebp($filePath);
                    break;
                } else {
                    return null;
                }

            default:
                return null;
        }
        if (!$srcImg) {
            return null;
        }
        $thumbImg = imagecreatetruecolor($newWidth, $newHeight);
        // Handle transparency for PNG/GIF
        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF])) {
            imagecolortransparent($thumbImg, imagecolorallocatealpha($thumbImg, 0, 0, 0, 127));
            imagealphablending($thumbImg, false);
            imagesavealpha($thumbImg, true);
        }
        imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumbImg, $thumbPath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbImg, $thumbPath, 6);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumbImg, $thumbPath);
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagewebp')) {
                    imagewebp($thumbImg, $thumbPath, 85);
                }
                break;
        }
        imagedestroy($srcImg);
        imagedestroy($thumbImg);
        return ['width' => $newWidth, 'height' => $newHeight, 'path' => $thumbPath];
    }


    // Ottieni un media per ID
    public function getById($id): object
    {
        $sql = "SELECT * FROM media WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, $this->db::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch($this->db::FETCH_OBJ);
    }

    // Lista media con filtri e paginazione
    public function getList($filters = [], $page = 1, $perPage = 24)
    {
        try {
            // Build main query for paginated items
            $sql = "SELECT * FROM media WHERE 1";
            $params = [];

            if (!empty($filters['type'])) {
                $sql .= " AND filetype LIKE :type";
                $params[':type'] = $filters['type'] . '/%';
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (title LIKE :search OR description LIKE :search OR filename LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            $sql .= " ORDER BY created_at DESC LIMIT :offset, :perPage";
            $offset = ($page - 1) * $perPage;

            $stmt = $this->db->prepare($sql);

            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
                LogHelper::debug("MediaModel::getList - Query parameter", [
                    'param' => $k,
                    'value' => $v
                ]);
            }

            $stmt->bindValue(':offset', $offset, $this->db::PARAM_INT);
            $stmt->bindValue(':perPage', $perPage, $this->db::PARAM_INT);

            $stmt->execute();
            $items = $stmt->fetchAll($this->db::FETCH_OBJ);

            // Build separate COUNT(*) query for total items
            $countSql = "SELECT COUNT(*) FROM media WHERE 1";
            if (!empty($filters['type'])) {
                $countSql .= " AND filetype LIKE :type";
            }
            if (!empty($filters['search'])) {
                $countSql .= " AND (title LIKE :search OR description LIKE :search OR filename LIKE :search)";
            }
            $countStmt = $this->db->prepare($countSql);
            foreach ($params as $k => $v) {
                $countStmt->bindValue($k, $v);
            }
            $countStmt->execute();
            $total = (int)$countStmt->fetchColumn();


            // Assicuriamoci che items sia sempre un array, anche vuoto
            if ($items === false) {
                $items = [];
                LogHelper::warning("MediaModel::getList - Items was false, converted to empty array");
            }

            return [
                'items' => $items,
                'total' => $total,
                'page' => $page,
                'totalPages' => ceil($total / $perPage)
            ];
        } catch (\Exception $e) {
            // Log dell'errore
            LogHelper::error("MediaModel::getList - Exception", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Restituisci un risultato vuoto ma valido in caso di errore
            return [
                'items' => [],
                'total' => 0,
                'page' => $page,
                'totalPages' => 0
            ];
        }
    }


    /**
     * Genera un nome file univoco per evitare sovrascritture
     * @param string $filename Nome del file originale
     * @return string Nome file univoco con estensione
     */
    private function generateUniqueFilename($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);

        // Rimuovi caratteri non alfanumerici e riduci i trattini multipli
        $basename = preg_replace('/[^a-zA-Z0-9-_]/', '-', $basename);
        $basename = preg_replace('/-+/', '-', $basename);
        $basename = trim($basename, '-');
        $basename = preg_replace('/-+/', '-', $basename);
        $basename = trim($basename, '-');

        $unique = $basename . '_' . uniqid() . '.' . strtolower($extension);

        return $unique;
    }

    /**
     * Elimina un media dal database e dal filesystem, e resetta i riferimenti negli articoli
     * @param int $id ID del media
     * @throws \Exception
     */
    public function deleteMedia($id)
    {
        $media = $this->getById($id);
        if (!$media) {
            throw new \Exception('Media non trovato');
        }
        // Cancella file principale
        $filePath = $this->uploadPath . ltrim($media->filepath, '/') . $media->filename;
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
        // Cancella thumbnail
        $thumbPath = $this->uploadPath . ltrim($media->filepath, '/') . 'thumbs/' . $media->filename;
        if (file_exists($thumbPath)) {
            @unlink($thumbPath);
        }
        // Reset featured_image nei post che usano questo media
        $sql = "UPDATE posts SET featured_image = NULL WHERE featured_image = :media_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':media_id', $filePath, $this->db::PARAM_STR);
        $stmt->execute();
        // Cancella record dal database
        $this->delete($id);
    }

    /**
     * Get allowed columns for ORDER BY clause
     *
     * @return array List of column names allowed in ORDER BY
     */
    protected function getAllowedOrderByColumns()
    {
        return ['id', 'filename', 'filetype', 'filesize', 'user_id', 'created_at', 'updated_at'];
    }
}
