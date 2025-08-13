<?php
/**
 * Evidence Model
 * Handles evidence/image uploads for complaints
 */

require_once 'BaseModel.php';

class Evidence extends BaseModel {
    protected $table = 'evidence';
    
    /**
     * Create evidence record
     */
    public function createEvidence($complaintId, $images = []) {
        $data = [
            'complaint_id' => $complaintId,
            'uploaded_at' => getCurrentDateTime()
        ];
        
        // Process up to 3 images
        for ($i = 1; $i <= 3; $i++) {
            $data["image_$i"] = isset($images[$i-1]) ? $images[$i-1] : null;
        }
        
        return $this->create($data);
    }
    
    /**
     * Find evidence by complaint ID
     */
    public function findByComplaintId($complaintId) {
        $stmt = $this->connection->prepare("SELECT * FROM evidence WHERE complaint_id = ?");
        $stmt->execute([$complaintId]);
        return $stmt->fetch();
    }
    
    /**
     * Update evidence images
     */
    public function updateEvidence($complaintId, $images = []) {
        $data = [];
        
        // Process up to 3 images
        for ($i = 1; $i <= 3; $i++) {
            if (isset($images[$i-1])) {
                $data["image_$i"] = $images[$i-1];
            }
        }
        
        if (empty($data)) {
            return false;
        }
        
        $columns = array_keys($data);
        $setClause = array_map(function($column) {
            return "$column = ?";
        }, $columns);
        
        $sql = "UPDATE evidence SET " . implode(', ', $setClause) . " WHERE complaint_id = ?";
        
        $params = array_values($data);
        $params[] = $complaintId;
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Delete evidence
     */
    public function deleteByComplaintId($complaintId) {
        // First get the images to delete files
        $evidence = $this->findByComplaintId($complaintId);
        
        if ($evidence) {
            // Delete physical files
            for ($i = 1; $i <= 3; $i++) {
                $imagePath = $evidence["image_$i"];
                if ($imagePath && file_exists(UPLOAD_DIR . $imagePath)) {
                    unlink(UPLOAD_DIR . $imagePath);
                }
            }
        }
        
        // Delete database record
        $stmt = $this->connection->prepare("DELETE FROM evidence WHERE complaint_id = ?");
        return $stmt->execute([$complaintId]);
    }
    
    /**
     * Handle file upload
     */
    public function handleFileUpload($files, $complaintId) {
        $uploadedFiles = [];
        $errors = [];
        
        if (!is_array($files['tmp_name'])) {
            // Single file
            $files = [
                'name' => [$files['name']],
                'tmp_name' => [$files['tmp_name']],
                'error' => [$files['error']],
                'size' => [$files['size']],
                'type' => [$files['type']]
            ];
        }
        
        $uploadDir = UPLOAD_DIR;
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        for ($i = 0; $i < count($files['tmp_name']) && $i < MAX_IMAGES_PER_COMPLAINT; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = $files['name'][$i];
                $tmpName = $files['tmp_name'][$i];
                $fileSize = $files['size'][$i];
                
                // Validate file
                $validation = $this->validateFile($fileName, $fileSize, $tmpName);
                if (!$validation['valid']) {
                    $errors[] = $validation['error'];
                    continue;
                }
                
                // Generate unique filename
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = $complaintId . '_' . ($i + 1) . '_' . time() . '.' . $extension;
                $targetPath = $uploadDir . $newFileName;
                
                // Move uploaded file
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $uploadedFiles[] = $newFileName;
                } else {
                    $errors[] = "Failed to upload: $fileName";
                }
            } else {
                $errors[] = "Upload error for: " . $files['name'][$i];
            }
        }
        
        // Create or update evidence record
        if (!empty($uploadedFiles)) {
            $existing = $this->findByComplaintId($complaintId);
            if ($existing) {
                $this->updateEvidence($complaintId, $uploadedFiles);
            } else {
                $this->createEvidence($complaintId, $uploadedFiles);
            }
        }
        
        return [
            'success' => !empty($uploadedFiles),
            'uploaded_files' => $uploadedFiles,
            'errors' => $errors
        ];
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($fileName, $fileSize, $tmpName) {
        // Check file size
        if ($fileSize > MAX_FILE_SIZE) {
            return [
                'valid' => false,
                'error' => "File $fileName is too large. Maximum size: " . round(MAX_FILE_SIZE / (1024 * 1024)) . "MB"
            ];
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            return [
                'valid' => false,
                'error' => "File $fileName has invalid extension. Allowed: " . implode(', ', ALLOWED_EXTENSIONS)
            ];
        }
        
        // Check if it's actually an image
        if (!getimagesize($tmpName)) {
            return [
                'valid' => false,
                'error' => "File $fileName is not a valid image"
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Get evidence images as array
     */
    public function getImages($complaintId) {
        $evidence = $this->findByComplaintId($complaintId);
        $images = [];
        
        if ($evidence) {
            for ($i = 1; $i <= 3; $i++) {
                $imagePath = $evidence["image_$i"];
                if ($imagePath && file_exists(UPLOAD_DIR . $imagePath)) {
                    $images[] = [
                        'filename' => $imagePath,
                        'url' => BASE_URL . UPLOAD_DIR . $imagePath,
                        'size' => filesize(UPLOAD_DIR . $imagePath)
                    ];
                }
            }
        }
        
        return $images;
    }
    
    /**
     * Delete specific image
     */
    public function deleteImage($complaintId, $imageIndex) {
        if ($imageIndex < 1 || $imageIndex > 3) {
            return false;
        }
        
        $evidence = $this->findByComplaintId($complaintId);
        if (!$evidence) {
            return false;
        }
        
        $imageField = "image_$imageIndex";
        $imagePath = $evidence[$imageField];
        
        if ($imagePath && file_exists(UPLOAD_DIR . $imagePath)) {
            unlink(UPLOAD_DIR . $imagePath);
        }
        
        // Update database to remove the image reference
        $stmt = $this->connection->prepare("UPDATE evidence SET $imageField = NULL WHERE complaint_id = ?");
        return $stmt->execute([$complaintId]);
    }
    
    /**
     * Clean up orphaned files (files without database records)
     */
    public function cleanupOrphanedFiles() {
        $uploadDir = UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            return ['deleted' => 0, 'errors' => []];
        }
        
        $files = glob($uploadDir . '*');
        $deleted = 0;
        $errors = [];
        
        // Get all image paths from database
        $stmt = $this->connection->prepare("
            SELECT image_1, image_2, image_3 FROM evidence 
            WHERE image_1 IS NOT NULL OR image_2 IS NOT NULL OR image_3 IS NOT NULL
        ");
        $stmt->execute();
        $evidenceRecords = $stmt->fetchAll();
        
        $validImages = [];
        foreach ($evidenceRecords as $record) {
            for ($i = 1; $i <= 3; $i++) {
                if ($record["image_$i"]) {
                    $validImages[] = $record["image_$i"];
                }
            }
        }
        
        foreach ($files as $file) {
            $fileName = basename($file);
            if (!in_array($fileName, $validImages)) {
                if (unlink($file)) {
                    $deleted++;
                } else {
                    $errors[] = "Could not delete: $fileName";
                }
            }
        }
        
        return ['deleted' => $deleted, 'errors' => $errors];
    }
}
?>
