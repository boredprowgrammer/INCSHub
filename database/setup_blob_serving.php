<?php
/**
 * Update script to modify image serving throughout the application
 * This creates helper functions for blob-based image serving
 */

require_once __DIR__ . '/../includes/config.php';

// Add helper functions to functions.php
$functions_content = file_get_contents(__DIR__ . '/../includes/functions.php');

// Check if blob image functions already exist
if (strpos($functions_content, 'function getImageUrl') === false) {
    
    $blob_functions = '

/**
 * Get image URL - either from blob storage or file system
 * @param string $type - featured, content, events
 * @param array $item - database record
 * @param string $fallback_field - field name for fallback file path
 * @return string - image URL
 */
function getImageUrl($type, $item, $fallback_field = null) {
    // Determine if we have blob data
    $has_blob = false;
    
    switch ($type) {
        case \'featured\':
            $has_blob = !empty($item[\'image_blob\']);
            break;
        case \'content\':
            $has_blob = !empty($item[\'featured_image_blob\']);
            break;
        case \'events\':
            $has_blob = !empty($item[\'featured_image_blob\']);
            break;
    }
    
    // Return blob URL if available
    if ($has_blob) {
        return \'/image.php?type=\' . $type . \'&id=\' . $item[\'id\'];
    }
    
    // Fallback to file system
    if ($fallback_field && !empty($item[$fallback_field])) {
        if ($type === \'featured\') {
            return escape($item[$fallback_field]);
        } else {
            return \'/uploads/images/\' . escape($item[$fallback_field]);
        }
    }
    
    return null;
}

/**
 * Check if item has image (blob or file)
 * @param string $type - featured, content, events  
 * @param array $item - database record
 * @param string $fallback_field - field name for fallback file path
 * @return bool
 */
function hasImage($type, $item, $fallback_field = null) {
    switch ($type) {
        case \'featured\':
            return !empty($item[\'image_blob\']) || !empty($item[$fallback_field]);
        case \'content\':
            return !empty($item[\'featured_image_blob\']) || !empty($item[$fallback_field]);
        case \'events\':
            return !empty($item[\'featured_image_blob\']) || !empty($item[$fallback_field]);
    }
    return false;
}
';

    // Append to functions.php
    file_put_contents(__DIR__ . '/../includes/functions.php', $blob_functions, FILE_APPEND);
    echo "Added blob image helper functions to functions.php\n";
    
} else {
    echo "Blob image functions already exist in functions.php\n";
}

echo "Image serving update complete!\n";
echo "\nNext steps:\n";
echo "1. Run database migration: php database/migrate_to_blob.php\n";  
echo "2. Apply database schema: mysql -u [user] -p [database] < database/migration_blob_storage.sql\n";
echo "3. Update admin upload forms to use blob storage\n";
echo "4. Test image serving with: /image.php?type=featured&id=1\n";
?>