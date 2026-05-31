<?php
require_once __DIR__ . '/config/config.php';
require_once APP_PATH . '/core/Database.php';

try {
    $pdo = Database::pdo();
    $teams = Database::all("SELECT * FROM teams");
    echo "Total teams: " . count($teams) . "\n";
    foreach ($teams as $t) {
        echo "ID: " . $t['id'] . " | Name: '" . $t['name'] . "' | City: '" . $t['city'] . "' | Captain: " . $t['captain_id'] . "\n";
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
