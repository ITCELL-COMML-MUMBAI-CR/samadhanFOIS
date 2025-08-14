<?php
/**
 * Evidence Model
 * Handles evidence/image uploads for complaints
 */

require_once 'BaseModel.php';
require_once __DIR__ . '/../utils/Logger.php';

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
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                $errors[] = 'Failed to create upload directory';
                Logger::error('Failed to create upload directory', ['dir' => $uploadDir]);
                return [
                    'success' => false,
                    'uploaded_files' => [],
                    'errors' => $errors
                ];
            }
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
                    Logger::warning('File validation failed', ['file' => $fileName, 'error' => $validation['error']]);
                    continue;
                }
                
                // Generate unique filename
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = $complaintId . '_' . ($i + 1) . '_' . time() . '.' . $extension;
                $targetPath = $uploadDir . $newFileName;
                
                // Compress and save the image
                if (move_uploaded_file($tmpName, $targetPath . '.original')) {
                    Logger::info('Uploaded original image', [
                        'file' => $fileName,
                        'saved_as' => $newFileName,
                        'original_size' => @filesize($targetPath . '.original')
                    ]);
                    // Compress the image
                    if ($this->compressImage($targetPath . '.original', $targetPath)) {
                        // Remove original file after successful compression
                        unlink($targetPath . '.original');
                        Logger::info('Compression successful', [
                            'file' => $newFileName,
                            'final_size' => @filesize($targetPath)
                        ]);
                        $uploadedFiles[] = $newFileName;
                    } else {
                        // If compression fails, use original file (fallback)
                        rename($targetPath . '.original', $targetPath);
                        $uploadedFiles[] = $newFileName;
                        $errors[] = "Warning: Could not compress $fileName, using original size";
                        Logger::warning('Compression failed, used original', [
                            'file' => $newFileName,
                            'size' => @filesize($targetPath)
                        ]);
                    }
                } else {
                    $errors[] = "Failed to upload: $fileName";
                    Logger::error('move_uploaded_file failed', [
                        'file' => $fileName,
                        'error_code' => $files['error'][$i] ?? null,
                        'target' => $targetPath . '.original'
                    ]);
                }
            } else {
                $errors[] = "Upload error for: " . $files['name'][$i] . ' (code ' . ($files['error'][$i] ?? 'unknown') . ')';
                Logger::warning('Upload error code', [
                    'file' => $files['name'][$i] ?? 'unknown',
                    'error_code' => $files['error'][$i] ?? null
                ]);
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
        
        // Note: File size check removed as we'll compress images automatically
        return ['valid' => true];
    }
    
    /**
     * Compress image to below 2MB while maintaining quality
     */
    private function compressImage($sourcePath, $targetPath, $maxSizeBytes = 2097152) { // 2MB = 2 * 1024 * 1024
        // Ensure GD extension is available
        if (!function_exists('imagecreatetruecolor') || !function_exists('imagecopyresampled')) {
            Logger::warning('GD extension not available, skipping compression');
            // Fallback to copying original
            return @copy($sourcePath, $targetPath);
        }
        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            Logger::warning('getimagesize failed', ['source' => $sourcePath]);
            return false;
        }
        
        $mime = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        // Create image resource based on type
        switch ($mime) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                Logger::warning('Unsupported image mime type', ['mime' => $mime, 'source' => $sourcePath]);
                return false;
        }
        
        if (!$sourceImage) {
            Logger::warning('Failed to create image resource', ['mime' => $mime, 'source' => $sourcePath]);
            return false;
        }
        
        // If original file is already under 2MB, just copy it
        if (filesize($sourcePath) <= $maxSizeBytes) {
            @copy($sourcePath, $targetPath);
            imagedestroy($sourceImage);
            return true;
        }
        
        // Calculate new dimensions while maintaining aspect ratio
        $maxWidth = 1920; // Max width for compression
        $maxHeight = 1080; // Max height for compression
        
        $ratio = min($maxWidth / $width, $maxHeight / $height, 1);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);
        
        // Create new image with calculated dimensions
        $compressedImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($mime == 'image/png' || $mime == 'image/gif') {
            imagealphablending($compressedImage, false);
            imagesavealpha($compressedImage, true);
            $transparent = imagecolorallocatealpha($compressedImage, 255, 255, 255, 127);
            imagefill($compressedImage, 0, 0, $transparent);
        }
        
        // Resize image
        imagecopyresampled($compressedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save compressed image with quality adjustment
        $quality = 85; // Start with 85% quality
        $attempt = 0;
        $maxAttempts = 5;
        
        do {
            $tempPath = $targetPath . '.tmp';
            
            switch ($mime) {
                case 'image/jpeg':
                    imagejpeg($compressedImage, $tempPath, $quality);
                    break;
                case 'image/png':
                    // PNG compression level (0-9, where 9 is max compression)
                    $pngQuality = (int)(9 - ($quality / 100) * 9);
                    imagepng($compressedImage, $tempPath, $pngQuality);
                    break;
                case 'image/gif':
                    imagegif($compressedImage, $tempPath);
                    break;
            }
            
            $attempt++;
            
            // Check if file size is acceptable
            if (file_exists($tempPath) && filesize($tempPath) <= $maxSizeBytes) {
                rename($tempPath, $targetPath);
                break;
            } else if ($attempt < $maxAttempts) {
                // Reduce quality for next attempt
                $quality -= 15;
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
            } else {
                // If we can't get it under 2MB, save with lowest quality
                if (file_exists($tempPath)) {
                    rename($tempPath, $targetPath);
                }
                break;
            }
            
        } while ($attempt < $maxAttempts);
        
        // Clean up resources
        imagedestroy($sourceImage);
        imagedestroy($compressedImage);
        
        $exists = file_exists($targetPath);
        if ($exists) {
            Logger::debug('Compression result', [
                'target' => $targetPath,
                'size' => @filesize($targetPath)
            ]);
        }
        return $exists;
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
                        'url' => BASE_URL . UPLOAD_URL . $imagePath,
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
