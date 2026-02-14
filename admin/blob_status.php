<?php
/**
 * Blob Storage Status Check
 * Shows which admin forms are using blob storage and migration status
 */

require_once __DIR__ . '/../includes/config.php';

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.status-ok { color: green; font-weight: bold; }
.status-pending { color: orange; font-weight: bold; }
.status-error { color: red; font-weight: bold; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
th { background-color: #f2f2f2; }
.code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; font-family: monospace; }
</style>";

echo "<h1>🗄️ Blob Storage Implementation Status</h1>";

// Check database schema
echo "<h2>📊 Database Schema Status</h2>";
try {
    // Check if blob columns exist
    $featured_columns = $db->query("SHOW COLUMNS FROM featured_images LIKE '%blob%'")->fetchAll();
    $content_columns = $db->query("SHOW COLUMNS FROM content LIKE '%blob%'")->fetchAll();
    $events_columns = $db->query("SHOW COLUMNS FROM events LIKE '%blob%'")->fetchAll();
    
    echo "<table>";
    echo "<tr><th>Table</th><th>Blob Columns</th><th>Status</th></tr>";
    echo "<tr><td>featured_images</td><td>" . count($featured_columns) . " columns</td><td class='" . (count($featured_columns) > 0 ? 'status-ok">✅ Ready' : 'status-pending">⏳ Needs Migration') . "</td></tr>";
    echo "<tr><td>content</td><td>" . count($content_columns) . " columns</td><td class='" . (count($content_columns) > 0 ? 'status-ok">✅ Ready' : 'status-pending">⏳ Needs Migration') . "</td></tr>";
    echo "<tr><td>events</td><td>" . count($events_columns) . " columns</td><td class='" . (count($events_columns) > 0 ? 'status-ok">✅ Ready' : 'status-pending">⏳ Needs Migration') . "</td></tr>";
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p class='status-error'>❌ Database connection error: " . $e->getMessage() . "</p>";
}

// Check admin forms
echo "<h2>🔧 Admin Form Status</h2>";
$admin_forms = [
    'Featured Images' => '/admin/featured-images.php',
    'Content (News)' => '/admin/content.php', 
    'Events' => '/admin/events.php'
];

echo "<table>";
echo "<tr><th>Form</th><th>Blob Support</th><th>Status</th></tr>";

foreach ($admin_forms as $name => $file) {
    $filepath = __DIR__ . '/..' . $file;
    if (file_exists($filepath)) {
        $content = file_get_contents($filepath);
        $has_blob = strpos($content, 'handleImageUpload') !== false;
        $has_blob_insert = strpos($content, 'image_blob') !== false;
        
        if ($has_blob && $has_blob_insert) {
            echo "<tr><td>$name</td><td class='status-ok'>✅ Full Blob Support</td><td class='status-ok'>Ready for uploads</td></tr>";
        } elseif ($has_blob) {
            echo "<tr><td>$name</td><td class='status-pending'>⚠️ Partial Support</td><td class='status-pending'>Needs database updates</td></tr>";
        } else {
            echo "<tr><td>$name</td><td class='status-error'>❌ No Blob Support</td><td class='status-error'>Needs form updates</td></tr>";
        }
    } else {
        echo "<tr><td>$name</td><td class='status-error'>❌ File Not Found</td><td class='status-error'>Missing file</td></tr>";
    }
}
echo "</table>";

// Check configuration
echo "<h2>⚙️ Configuration Status</h2>";
echo "<table>";
echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";
echo "<tr><td>USE_BLOB_STORAGE</td><td class='code'>" . (defined('USE_BLOB_STORAGE') ? (USE_BLOB_STORAGE ? 'true' : 'false') : 'undefined') . "</td><td class='" . (defined('USE_BLOB_STORAGE') && USE_BLOB_STORAGE ? 'status-ok">✅ Enabled' : 'status-pending">⏳ Check config') . "</td></tr>";
echo "<tr><td>KEEP_FILE_BACKUP</td><td class='code'>" . (defined('KEEP_FILE_BACKUP') ? (KEEP_FILE_BACKUP ? 'true' : 'false') : 'undefined') . "</td><td class='" . (defined('KEEP_FILE_BACKUP') ? 'status-ok">✅ Configured' : 'status-pending">⏳ Check config') . "</td></tr>";
echo "</table>";

// Check image serving endpoint
echo "<h2>🖼️ Image Serving Status</h2>";
$image_endpoint = __DIR__ . '/../public/image.php';
if (file_exists($image_endpoint)) {
    echo "<p class='status-ok'>✅ Image serving endpoint exists: <span class='code'>/image.php</span></p>";
} else {
    echo "<p class='status-error'>❌ Image serving endpoint missing</p>";
}

// Show current blob data
echo "<h2>📈 Current Blob Data</h2>";
try {
    $featured_blob_count = $db->query("SELECT COUNT(*) FROM featured_images WHERE image_blob IS NOT NULL")->fetchColumn();
    $content_blob_count = $db->query("SELECT COUNT(*) FROM content WHERE featured_image_blob IS NOT NULL")->fetchColumn();
    $events_blob_count = $db->query("SELECT COUNT(*) FROM events WHERE featured_image_blob IS NOT NULL")->fetchColumn();
    
    echo "<table>";
    echo "<tr><th>Table</th><th>Items with Blobs</th><th>Total Items</th><th>Percentage</th></tr>";
    
    $featured_total = $db->query("SELECT COUNT(*) FROM featured_images")->fetchColumn();
    $content_total = $db->query("SELECT COUNT(*) FROM content WHERE featured_image IS NOT NULL OR featured_image_blob IS NOT NULL")->fetchColumn();
    $events_total = $db->query("SELECT COUNT(*) FROM events WHERE featured_image IS NOT NULL OR featured_image_blob IS NOT NULL")->fetchColumn();
    
    echo "<tr><td>featured_images</td><td>$featured_blob_count</td><td>$featured_total</td><td>" . ($featured_total > 0 ? round(($featured_blob_count/$featured_total)*100) : 0) . "%</td></tr>";
    echo "<tr><td>content</td><td>$content_blob_count</td><td>$content_total</td><td>" . ($content_total > 0 ? round(($content_blob_count/$content_total)*100) : 0) . "%</td></tr>";
    echo "<tr><td>events</td><td>$events_blob_count</td><td>$events_total</td><td>" . ($events_total > 0 ? round(($events_blob_count/$events_total)*100) : 0) . "%</td></tr>";
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p class='status-error'>❌ Error checking blob data: " . $e->getMessage() . "</p>";
}

// Migration instructions
echo "<h2>🚀 Next Steps</h2>";
if (count($featured_columns) == 0) {
    echo "<ol>";
    echo "<li><strong>Apply database migration:</strong><br><span class='code'>mysql -u root -p church_news_hub < database/migration_blob_storage.sql</span></li>";
    echo "<li><strong>Run image migration:</strong><br><span class='code'>php database/migrate_to_blob.php</span></li>";
    echo "<li><strong>Test blob serving:</strong><br>Visit <span class='code'>/image.php?type=featured&id=1</span></li>";
    echo "</ol>";
} else {
    echo "<p class='status-ok'>✅ <strong>System is ready!</strong> All new uploads will automatically use blob storage.</p>";
    if ($featured_blob_count + $content_blob_count + $events_blob_count == 0) {
        echo "<p class='status-pending'>⏳ <strong>Existing images:</strong> Run migration to convert existing files: <span class='code'>php database/migrate_to_blob.php</span></p>";
    }
}
?>