<?php
// import_schema.php - imports schema.sql using mysqli->multi_query
require 'database.php';

$host = 'localhost';
$user = 'root';
$pass = 'Himanshu@1310';
$db = null; // We'll create DB first if needed

$sqlFile = __DIR__ . DIRECTORY_SEPARATOR . 'schema.sql';
if (!file_exists($sqlFile)) {
    echo "schema.sql not found: $sqlFile\n";
    exit(1);
}

$content = file_get_contents($sqlFile);
if ($content === false) {
    echo "Failed to read schema.sql\n";
    exit(1);
}

// Connect without selecting DB to allow CREATE DATABASE
$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_errno) {
    echo "MySQL connection failed: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "\n";
    exit(1);
}

// Try to extract the CREATE DATABASE name from the file (simple heuristic)
if (preg_match('/CREATE\s+DATABASE\s+IF\s+NOT\s+EXISTS\s+`?([a-zA-Z0-9_]+)`?/i', $content, $m)) {
    $dbname = $m[1];
} elseif (preg_match('/USE\s+`?([a-zA-Z0-9_]+)`?/i', $content, $m2)) {
    $dbname = $m2[1];
} else {
    $dbname = 'erp_system';
}

// Create DB if not exists
if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
    echo "Failed to create database $dbname: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
    $mysqli->close();
    exit(1);
}

// Select DB
if (!$mysqli->select_db($dbname)) {
    echo "Failed to select database $dbname: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
    $mysqli->close();
    exit(1);
}

// Remove DELIMITER statements and handle ; separated statements
// A basic approach: split on ";\n" not inside strings — we'll use multi_query on the whole content.

if ($mysqli->multi_query($content)) {
    do {
        if ($res = $mysqli->store_result()) {
            // consume result
            $res->free();
        }
        // If there are more results, continue
    } while ($mysqli->more_results() && $mysqli->next_result());

    if ($mysqli->errno) {
        echo "Import completed with errors: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
        $mysqli->close();
        exit(1);
    }

    echo "Import successful into database: $dbname\n";
    $mysqli->close();
    exit(0);
} else {
    echo "Import failed: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
    $mysqli->close();
    exit(1);
}

