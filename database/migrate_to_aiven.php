<?php
/**
 * Migration Script: Local MySQL → Aiven Cloud MySQL
 * 
 * This script:
 * 1. Connects to the LOCAL database to export schema + data
 * 2. Connects to the AIVEN database to import everything
 * 
 * Usage: php database/migrate_to_aiven.php
 */

// ── Local DB credentials (source) ──
$localHost = 'localhost';
$localPort = 3306;
$localDb   = 'church_news_hub';
$localUser = 'root';
$localPass = 'rootUser123';

// ── Aiven DB credentials (target) ──
$aivenHost = 'neoera-proton-31c4.e.aivencloud.com';
$aivenPort = 23691;
$aivenDb   = '17852Hub';
$aivenUser = 'avnadmin';
$aivenPass = 'AVNS_d1Esbs4iMnGULVJLDdx';

// Tables in dependency order (parents before children)
$tables = [
    'admins',
    'categories',
    'settings',
    'content',
    'links',
    'events',
    'telegram_officers',
    'featured_images',
];

// ─────────────────────────────────────────────
echo "╔══════════════════════════════════════════════╗\n";
echo "║   INCS Hub — Aiven Cloud Migration Tool     ║\n";
echo "╚══════════════════════════════════════════════╝\n\n";

// ── Step 1: Connect to local database ──
echo "→ Connecting to LOCAL database ($localHost:$localPort/$localDb)...\n";
try {
    $local = new PDO(
        "mysql:host=$localHost;port=$localPort;dbname=$localDb;charset=utf8mb4",
        $localUser, $localPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    echo "  ✓ Local connection OK\n\n";
} catch (PDOException $e) {
    die("  ✗ Local connection failed: " . $e->getMessage() . "\n");
}

// ── Step 2: Connect to Aiven database ──
echo "→ Connecting to AIVEN database ($aivenHost:$aivenPort/$aivenDb)...\n";
try {
    $aiven = new PDO(
        "mysql:host=$aivenHost;port=$aivenPort;dbname=$aivenDb;charset=utf8mb4",
        $aivenUser, $aivenPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            PDO::MYSQL_ATTR_SSL_CA => true,
        ]
    );
    echo "  ✓ Aiven connection OK\n\n";
} catch (PDOException $e) {
    die("  ✗ Aiven connection failed: " . $e->getMessage() . "\n");
}

// ── Step 3: Disable foreign key checks on Aiven ──
$aiven->exec("SET FOREIGN_KEY_CHECKS = 0");
$aiven->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'");
$aiven->exec("SET NAMES utf8mb4");

// ── Step 4: Get CREATE TABLE statements from local and create on Aiven ──
echo "═══ PHASE 1: Schema Migration ═══\n\n";

foreach ($tables as $table) {
    echo "  → Creating table '$table'...\n";
    
    // Get CREATE TABLE from local
    $stmt = $local->query("SHOW CREATE TABLE `$table`");
    $row = $stmt->fetch();
    $createSql = $row['Create Table'];
    
    // Drop existing table on Aiven (if any)
    $aiven->exec("DROP TABLE IF EXISTS `$table`");
    
    // Create table on Aiven
    try {
        $aiven->exec($createSql);
        echo "    ✓ Table '$table' created\n";
    } catch (PDOException $e) {
        echo "    ✗ Failed: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// ── Step 5: Migrate data table by table ──
echo "═══ PHASE 2: Data Migration ═══\n\n";

$totalRows = 0;

foreach ($tables as $table) {
    // Count rows in local
    $countStmt = $local->query("SELECT COUNT(*) as cnt FROM `$table`");
    $count = $countStmt->fetch()['cnt'];
    
    echo "  → Migrating '$table' ($count rows)...\n";
    
    if ($count == 0) {
        echo "    ⊘ Skipped (empty)\n";
        continue;
    }
    
    // Get all data from local
    $dataStmt = $local->query("SELECT * FROM `$table`");
    $rows = $dataStmt->fetchAll();
    
    // Get column names
    $columns = array_keys($rows[0]);
    $colList = implode('`, `', $columns);
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    
    $insertSql = "INSERT INTO `$table` (`$colList`) VALUES ($placeholders)";
    $insertStmt = $aiven->prepare($insertSql);
    
    $migrated = 0;
    $errors = 0;
    
    foreach ($rows as $row) {
        try {
            $insertStmt->execute(array_values($row));
            $migrated++;
        } catch (PDOException $e) {
            $errors++;
            if ($errors <= 3) {
                echo "    ⚠ Row error: " . substr($e->getMessage(), 0, 100) . "\n";
            }
        }
    }
    
    $totalRows += $migrated;
    echo "    ✓ Migrated $migrated/$count rows";
    if ($errors > 0) echo " ($errors errors)";
    echo "\n";
}

// ── Step 6: Re-enable foreign key checks ──
$aiven->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\n═══ PHASE 3: Verification ═══\n\n";

// Verify row counts match
$allGood = true;
foreach ($tables as $table) {
    $localCount = $local->query("SELECT COUNT(*) as cnt FROM `$table`")->fetch()['cnt'];
    
    try {
        $aivenCount = $aiven->query("SELECT COUNT(*) as cnt FROM `$table`")->fetch()['cnt'];
    } catch (PDOException $e) {
        $aivenCount = '?';
    }
    
    $status = ($localCount == $aivenCount) ? '✓' : '✗';
    if ($localCount != $aivenCount) $allGood = false;
    
    printf("  %s %-25s Local: %4s → Aiven: %4s\n", $status, $table, $localCount, $aivenCount);
}

echo "\n╔══════════════════════════════════════════════╗\n";
if ($allGood) {
    echo "║  ✓ Migration completed successfully!         ║\n";
    echo "║  Total rows migrated: " . str_pad($totalRows, 22) . " ║\n";
} else {
    echo "║  ⚠ Migration completed with differences     ║\n";
}
echo "╚══════════════════════════════════════════════╝\n\n";

echo "Your app config (includes/config.php) is already\n";
echo "pointing to the Aiven database. You're all set!\n\n";
