<?php
/**
 * Upload Configuration
 * Controls how images are stored (blob vs file system)
 */

// Image storage configuration
define('USE_BLOB_STORAGE', true); // Set to false to use file system only
define('KEEP_FILE_BACKUP', true); // Set to false to only use blob (no file backup)

// Upload settings
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('UPLOAD_DIR_FEATURED', __DIR__ . '/../uploads/featured-images/');
define('UPLOAD_DIR_CONTENT', __DIR__ . '/../uploads/images/');

/**
 * Handle image upload with blob storage support
 * @param array $file - $_FILES array for the uploaded file
 * @param string $upload_dir - Directory to save file backup
 * @return array - Contains image data for database storage
 */
function handleImageUpload($file, $upload_dir = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Validate file type
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, ALLOWED_IMAGE_TYPES)) {
        throw new Exception('Invalid file type. Only JPEG, PNG, WebP, and GIF images are allowed.');
    }
    
    // Validate file size
    if ($file['size'] > MAX_IMAGE_SIZE) {
        throw new Exception('File too large. Maximum size is ' . (MAX_IMAGE_SIZE / 1024 / 1024) . 'MB.');
    }
    
    $result = [
        'image_data' => null,
        'image_path' => '',
        'mime_type' => $fileType,
        'size' => $file['size'],
        'filename' => basename($file['name'])
    ];
    
    // Read image data for blob storage
    if (USE_BLOB_STORAGE) {
        $result['image_data'] = file_get_contents($file['tmp_name']);
    }
    
    // Save file backup if enabled
    if (KEEP_FILE_BACKUP && $upload_dir) {
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $fileName = time() . '_' . $result['filename'];
        $uploadPath = $upload_dir . $fileName;
        $result['image_path'] = str_replace(__DIR__ . '/..', '', $uploadPath);
        
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            if (!USE_BLOB_STORAGE) {
                throw new Exception('Failed to upload image');
            }
            // If blob storage is enabled, file failure is not critical
            $result['image_path'] = '';
        }
    }
    
    return $result;
}

/**
 * Get image URL based on storage configuration
 * @param array $item - Database record
 * @param string $type - Image type (featured, content, events)
 * @param string $fallback_field - Field name for file path
 * @return string|null
 */
function getImageDisplayUrl($item, $type, $fallback_field = 'image_path') {
    // Check if we have blob data
    $blob_field = $type === 'featured' ? 'image_blob' : 'featured_image_blob';
    
    if (!empty($item[$blob_field])) {
        return '/image.php?type=' . $type . '&id=' . $item['id'];
    }
    
    // Fallback to file system
    if (!empty($item[$fallback_field])) {
        if ($type === 'featured') {
            return $item[$fallback_field];
        } else {
            return '/uploads/images/' . $item[$fallback_field];
        }
    }
    
    return null;
}
?>