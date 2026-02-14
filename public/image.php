<?php
/**
 * Image serving endpoint for blob storage
 * Serves images directly from database blob storage
 */

require_once __DIR__ . '/../includes/config.php';

// Get parameters
$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$table = '';
$blob_column = '';
$mime_column = '';

// Validate request parameters
if (empty($type) || $id <= 0) {
    http_response_code(400);
    die('Invalid parameters');
}

// Determine table and columns based on type
switch ($type) {
    case 'featured':
        $table = 'featured_images';
        $blob_column = 'image_blob';
        $mime_column = 'image_mime_type';
        break;
    case 'content':
        $table = 'content';
        $blob_column = 'featured_image_blob';
        $mime_column = 'featured_image_mime_type';
        break;
    case 'events':
        $table = 'events';
        $blob_column = 'featured_image_blob';
        $mime_column = 'featured_image_mime_type';
        break;
    default:
        http_response_code(400);
        die('Invalid image type');
}

try {
    // Get image data from database
    $stmt = $db->query("SELECT {$blob_column}, {$mime_column} FROM {$table} WHERE id = ? AND {$blob_column} IS NOT NULL", [$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result || empty($result[$blob_column])) {
        http_response_code(404);
        die('Image not found');
    }

    $image_data = $result[$blob_column];
    $mime_type = $result[$mime_column] ?? 'image/jpeg';

    // Set appropriate headers
    header('Content-Type: ' . $mime_type);
    header('Content-Length: ' . strlen($image_data));
    header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
    header('ETag: "' . md5($image_data) . '"');
    
    // Check if client has cached version
    $client_etag = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
    if ($client_etag === '"' . md5($image_data) . '"') {
        http_response_code(304);
        exit;
    }

    // Output image data
    echo $image_data;

} catch (Exception $e) {
    error_log("Image serving error: " . $e->getMessage());
    http_response_code(500);
    die('Server error');
}
?>