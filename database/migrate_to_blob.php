<?php
/**
 * Migration script to convert existing file-based images to blob storage
 * This script reads existing images from the filesystem and stores them as blobs in the database
 */

require_once __DIR__ . '/../includes/config.php';

function migrateImagesToBlob() {
    global $db;
    
    echo "Starting image to blob migration...\n";
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Migrate featured_images table
        echo "Migrating featured_images...\n";
        $stmt = $db->query("SELECT id, image_path FROM featured_images WHERE image_path IS NOT NULL AND image_blob IS NULL");
        $featured_images = $stmt->fetchAll();
        
        foreach ($featured_images as $image) {
            $file_path = __DIR__ . '/../public' . $image['image_path'];
            if (file_exists($file_path)) {
                $image_data = file_get_contents($file_path);
                $image_info = getimagesize($file_path);
                $mime_type = $image_info['mime'] ?? 'image/jpeg';
                $size = filesize($file_path);
                $filename = basename($file_path);
                
                $update_stmt = $db->prepare("
                    UPDATE featured_images 
                    SET image_blob = ?, image_mime_type = ?, image_size = ?, original_filename = ? 
                    WHERE id = ?
                ");
                $update_stmt->execute([$image_data, $mime_type, $size, $filename, $image['id']]);
                
                echo "Migrated featured image ID {$image['id']}: {$filename}\n";
            } else {
                echo "Warning: File not found for featured image ID {$image['id']}: {$file_path}\n";
            }
        }
        
        // Migrate content table featured images
        echo "Migrating content featured images...\n";
        $stmt = $db->query("SELECT id, featured_image FROM content WHERE featured_image IS NOT NULL AND featured_image_blob IS NULL");
        $content_images = $stmt->fetchAll();
        
        foreach ($content_images as $content) {
            $file_path = __DIR__ . '/../public/uploads/images/' . $content['featured_image'];
            if (file_exists($file_path)) {
                $image_data = file_get_contents($file_path);
                $image_info = getimagesize($file_path);
                $mime_type = $image_info['mime'] ?? 'image/jpeg';
                $size = filesize($file_path);
                $filename = $content['featured_image'];
                
                $update_stmt = $db->prepare("
                    UPDATE content 
                    SET featured_image_blob = ?, featured_image_mime_type = ?, featured_image_size = ?, featured_image_filename = ? 
                    WHERE id = ?
                ");
                $update_stmt->execute([$image_data, $mime_type, $size, $filename, $content['id']]);
                
                echo "Migrated content image ID {$content['id']}: {$filename}\n";
            } else {
                echo "Warning: File not found for content ID {$content['id']}: {$file_path}\n";
            }
        }
        
        // Migrate events table featured images
        echo "Migrating events featured images...\n";
        $stmt = $db->query("SELECT id, featured_image FROM events WHERE featured_image IS NOT NULL AND featured_image_blob IS NULL");
        $events_images = $stmt->fetchAll();
        
        foreach ($events_images as $event) {
            $file_path = __DIR__ . '/../public/uploads/images/' . $event['featured_image'];
            if (file_exists($file_path)) {
                $image_data = file_get_contents($file_path);
                $image_info = getimagesize($file_path);
                $mime_type = $image_info['mime'] ?? 'image/jpeg';
                $size = filesize($file_path);
                $filename = $event['featured_image'];
                
                $update_stmt = $db->prepare("
                    UPDATE events 
                    SET featured_image_blob = ?, featured_image_mime_type = ?, featured_image_size = ?, featured_image_filename = ? 
                    WHERE id = ?
                ");
                $update_stmt->execute([$image_data, $mime_type, $size, $filename, $event['id']]);
                
                echo "Migrated event image ID {$event['id']}: {$filename}\n";
            } else {
                echo "Warning: File not found for event ID {$event['id']}: {$file_path}\n";
            }
        }
        
        // Commit transaction
        $db->commit();
        echo "Migration completed successfully!\n";
        
        // Show summary
        $featured_count = $db->query("SELECT COUNT(*) FROM featured_images WHERE image_blob IS NOT NULL")->fetchColumn();
        $content_count = $db->query("SELECT COUNT(*) FROM content WHERE featured_image_blob IS NOT NULL")->fetchColumn();
        $events_count = $db->query("SELECT COUNT(*) FROM events WHERE featured_image_blob IS NOT NULL")->fetchColumn();
        
        echo "\nSummary:\n";
        echo "Featured images migrated: {$featured_count}\n";
        echo "Content images migrated: {$content_count}\n";
        echo "Event images migrated: {$events_count}\n";
        echo "Total images migrated: " . ($featured_count + $content_count + $events_count) . "\n";
        
    } catch (Exception $e) {
        $db->rollBack();
        echo "Migration failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Check if running from command line
if (php_sapi_name() === 'cli') {
    migrateImagesToBlob();
} else {
    // If accessed via web, show simple interface
    if (isset($_GET['migrate']) && $_GET['migrate'] === 'confirm') {
        echo "<pre>";
        migrateImagesToBlob();
        echo "</pre>";
    } else {
        echo "<html><body>";
        echo "<h2>Image to Blob Migration</h2>";
        echo "<p>This will migrate all existing images to blob storage.</p>";
        echo "<p><strong>Warning:</strong> This operation cannot be undone easily. Make sure you have a database backup.</p>";
        echo "<a href='?migrate=confirm' onclick=\"return confirm('Are you sure you want to migrate all images to blob storage? This cannot be undone easily.')\">Start Migration</a>";
        echo "</body></html>";
    }
}
?>