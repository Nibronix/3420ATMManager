<?php

// Debugging tools
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);

$data = json_decode(file_get_contents('php://input'), true);

// Set the response type to JSON
header('Content-Type: application/json');

// Longitude and Latitude
$longitude = $data['longitude'];
$latitude = $data['latitude'];

// Connect to the database
$db = new PDO('mysql:host=localhost;dbname=atmmanager', 'atmmanager', 'Zyaf&6yud');

// Google Places API URL and Key
$apiKey = 'AIzaSyCeBX0YxeVU3hNGHRCUNCdZYxF_jyGW410';
$googlePlacesUrl = "https://maps.googleapis.com/maps/api/place/nearbysearch/json";

// Prepare request to Google Places API
$queryParams = [
    'location' => implode(',', [$latitude, $longitude]),
    'radius' => 2000,
    'type' => 'atm',
    'key' => $apiKey
];

// Use cURL for the Google Places API request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $googlePlacesUrl . '?' . http_build_query($queryParams));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

// Check for errors
if (!$response) {
    die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
}
curl_close($ch);

// Decode the response
$atmData = json_decode($response, true);

// Initialize the variable
$allInsertsSuccessful = true;

// Store SQL queries for debugging
$debugQueries = [];

// Process and insert ATM data into the database
foreach ($atmData['results'] as $atm) {

    // Extract ATM details
    $name = $atm['name']; // Name of the ATM
    $location = $atm['vicinity']; // Location name
    $latitude = $atm['geometry']['location']['lat']; // Latitude
    $longitude = $atm['geometry']['location']['lng']; // Longitude

    // Round coordinates to match database precision
    $roundedLatitude = round($latitude, 6);
    $roundedLongitude = round($longitude, 6);

    // Placeholder values for hours and bank as they might not be available in the Google Places API response
    $start_hours = "00:00:00";
    $close_hours = "00:00:00";
    $bank = "Unknown";

    
    // Get place details for Start_Hours and Close_Hours
    $placeId = $atm['place_id'];
    $placeDetailsUrl = "https://maps.googleapis.com/maps/api/place/details/json";
    $placeDetailsParams = [
        'place_id' => $placeId,
        'fields' => 'opening_hours',
        'key' => $apiKey
    ];

    // Use cURL for the Google Places Details API request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $placeDetailsUrl . '?' . http_build_query($placeDetailsParams));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the request
    $response = curl_exec($ch);
    if (!$response) {
        die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
    }
    curl_close($ch);

    // Decode the response
    $placeDetails = json_decode($response, true);

    // Check if opening hours are available
    if (isset($placeDetails['result']['opening_hours']['periods'])) {
        // Get the opening hours
        $openingHours = $placeDetails['result']['opening_hours']['periods'];

        // Assuming the first period is the regular opening hours
        if (isset($openingHours[0])) {
            $start_hours = $openingHours[0]['open']['time']; // Opening time

            // Check if closing time is set
            if (isset($openingHours[0])) {
                $start_hours = $openingHours[0]['open']['time']; // Opening time
                if (isset($openingHours[0]['close'])) {
                    $close_hours = $openingHours[0]['close']['time']; // Closing time
                } else {
                    $close_hours = '00:00:00'; // Default value if closing time is not set
                }
            }

        }
    }
    

    // Check if an ATM with the same latitude and longitude already exists.
    // Long and lat values are rounded to 6 decimal places to avoid floating point precision issues.
    $checkQuery = $db->prepare(
        "SELECT COUNT(*) FROM ATM WHERE ROUND(Latitude, 6) = :latitude AND ROUND(Longitude, 6) = :longitude");

    $checkQuery->bindParam(':latitude', $roundedLatitude);
    $checkQuery->bindParam(':longitude', $roundedLongitude);
    $checkQuery->execute();

    // If an ATM with the same coordinates doesn't exist, insert the new ATM
    if ($checkQuery->fetchColumn() == 0) {
        $query = $db->prepare(
            "INSERT INTO ATM (Name, Location, Latitude, Longitude, Start_Hours, Close_Hours) 
            VALUES (:name, :location, :latitude, :longitude, :start_hours, :close_hours)");

        $query->bindParam(':name', $name);
        $query->bindParam(':location', $location);
        $query->bindParam(':latitude', $roundedLatitude);
        $query->bindParam(':longitude', $roundedLongitude);
        $query->bindParam(':start_hours', $start_hours);
        $query->bindParam(':close_hours', $close_hours);

        // Create the full query string for debugging
        $debugQuery = str_replace([':name', ':location', ':roundedLatitude', ':roundedLongitude'],
        ["'".$name."'", "'".$location."'", $roundedLatitude, $roundedLongitude], 
        "INSERT INTO ATM (Name, Location, Latitude, Longitude) VALUES 
        (:name, :location, :roundedLatitude, :roundedLongitude)");

        // Add the query to the debugQueries array
        $debugQueries[] = $debugQuery;

        // Execute the query
        if (!$query->execute()) {
            // Handle errors
            $errorInfo = $query->errorInfo();
            error_log(print_r($errorInfo, true));
        }
    }
}

// Check if all inserts were successful
if ($allInsertsSuccessful) {
    echo json_encode(['status' => 'success', 'debugQueries' => $debugQueries, 'atmData' => $atmData]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to insert data into the database.', 
    'debugQueries' => $debugQueries, 'atmData' => $atmData]);
}


?>
