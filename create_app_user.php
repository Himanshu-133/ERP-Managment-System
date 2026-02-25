<?php
// create_app_user.php - creates a DB user and grants privileges for erp_system
$rootUser = 'root';
$rootPass = 'Himanshu@1310';
$appUser = 'erp_user';
$appPass = 'Himanshu@1310';
$db = 'erp_system';

$mysqli = new mysqli('127.0.0.1', $rootUser, $rootPass);
if ($mysqli->connect_errno) {
    echo "Root connect failed: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "\n";
    exit(1);
}

$queries = [
    "CREATE USER IF NOT EXISTS '$appUser'@'localhost' IDENTIFIED BY '$appPass'",
    "CREATE USER IF NOT EXISTS '$appUser'@'127.0.0.1' IDENTIFIED BY '$appPass'",
    "GRANT ALL PRIVILEGES ON `$db`.* TO '$appUser'@'localhost'",
    "GRANT ALL PRIVILEGES ON `$db`.* TO '$appUser'@'127.0.0.1'",
    "FLUSH PRIVILEGES"
];
foreach ($queries as $q) {
    if (!$mysqli->query($q)) {
        echo "Query failed: $q => (" . $mysqli->errno . ") " . $mysqli->error . "\n";
    } else {
        echo "OK: $q\n";
    }
}
$mysqli->close();
