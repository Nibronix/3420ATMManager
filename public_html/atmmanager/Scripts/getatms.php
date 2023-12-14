<?php

// Debugging tools
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connect to the database
$db = new PDO('mysql:host=localhost;dbname=atmmanager', 'atmmanager', 'Zyaf&6yud');
$query = $db->query("SELECT Name, Location, Start_Hours, Close_Hours, Latitude, Longitude FROM ATM");
$atms = $query->fetchAll(PDO::FETCH_ASSOC);

// Output the result as JSON
$json = json_encode($atms);

// Check if json_encode failed
if ($json === false) {
    // json_encode failed
    echo 'json_encode error: ' . json_last_error_msg();
} else {
    // json_encode succeeded, output the result
    echo $json;
}

?>