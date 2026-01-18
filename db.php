
<?php

// zakonisht root në XAMPP
// zakonisht bosh në XAMPP
// ndrysho me emrin e DB tënde
require_once 'config.php';

$host = DB_HOST;
$dbname = DB_NAME;
$username = DB_USER;
$password = DB_PASS;
try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
