<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $PROJECT_NAME ?></title>
    <link rel="stylesheet" href="style.css">

    <?php $PROJECT_NAME = 'ATM Manager'; ?>
    

    <!-- Mapbox CSS -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.0.0/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.0.0/mapbox-gl.js"></script>

    <!-- Mapbox Geocoder -->
    <script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js'></script>
    <link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css' type='text/css' />

    <!-- Mapbox JS -->
    <style>
        #map {position: absolute; top: 30%; bottom: 5%; left: 10%; width: 80%;}

        #addressInput, button {
        margin: 0.5em;
        }

    </style>

</head>
<body>
<h1><?= $PROJECT_NAME ?></h1>
    <?php include("nav.php"); ?>
    <?php echo "<br>"; ?>

    <!-- Mapbox JS -->
    <div id="map"></div>
    <script>

        /* Debugging tools
        ini_set('display_errors', 1);
        ini_set('log_errors', 1);
        ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
        error_reporting(E_ALL);
        */

        // Mapbox JS
        mapboxgl.accessToken = 'pk.eyJ1Ijoibm1hcm9sbGEiLCJhIjoiY2xwcm5iejJ1MGFkNDJrbzdvc2V6eTY0cSJ9.4cxz5sdYOnx-vzmg94dpkw';
        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [-96, 37.8],
            zoom: 3
        });

        // This function calculates the approximate distance in kilometers between two sets of coordinates (lat1, lon1) and (lat2, lon2).
        // It's used to calculate the distance between a user's searched address and the ATMs in a database.
        function getApproximateDistanceInKm(lat1, lon1, lat2, lon2) {
            var x = lat2 - lat1;
            var y = (lon2 - lon1) * Math.cos(lat1);
            return 111.2 * Math.sqrt(x*x + y*y);
        }

        // Sends coordinates to googleplaces.php where it will process ATM data
        function sendCoordinatesToPHP(coordinates) {
            return new Promise((resolve, reject) => {
                fetch('./Scripts/googleplaces.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(coordinates)
                })
                .then(response => response.text())
                .then(text => {
                    if (!text) {
                    console.error('Empty response');
                    return;
                }

                    try {
                        const jsonResponse = JSON.parse(text);
                    } catch (error) {
                        console.error('JSON parsing error:', error, 'Response:', text);
                        throw error;
                    }

                    // Parse the response
                    const responseData = JSON.parse(text);

                    // Debugging
                    console.log("Activating sendCoordinatesToPHP!");
                    console.log("Debug:", text);
                    console.log("Debug SQL queries:", responseData.debugQueries);
                    console.log("ATM Data:", responseData.atmData);

                    if (responseData.status === 'error') {
                        console.error('Failed to insert data:', responseData.message);
                        reject(responseData.message);
                    } else {
                        resolve(responseData);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    reject(error);
                });
            });
        }

        // Sets current markers to an empty array
        currentMarkers = [];

        // Fetches markers from the server
        function fetchMarkers(longitude, latitude) {
            console.log("Activating fetchMarkers!");
            fetch(`./Scripts/getatms.php`)
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    // Return data so it can be used in the next .then() block
                    return data;
                } catch (error) {
                    console.error('Error parsing JSON:', error, 'Response:', text);
                }
            })
            .then(data => {
                if (typeof data === 'undefined') {
                    console.error('Data is undefined');
                    return;
                }

                // For each ATM, create a marker and add it to the map
                data.forEach(atm => {
                    console.log(atm);

                    // Parse the coordinates
                    const atmLongitude = parseFloat(atm.Longitude);
                    const atmLatitude = parseFloat(atm.Latitude);
                    const distance = getApproximateDistanceInKm(latitude, longitude, atmLatitude, atmLongitude);

                    // Formats the time from 24 hour to 12 hour.
                    // The SQL database format is weird because the minutes are hours and the seconds are minutes.
                    // Example: 00:08:30 is actually 8:30 AM. 00:16:30 is actually 4:30 PM.
                    // Might be a better way to do this.
                    const formatTime12Hour = (time24) => {
                        if (time24 === null) {
                            return '00:00';
                        }
                        let [hours, minutes, seconds] = time24.split(':');
                        let dt = new Date();
                        dt.setHours(+minutes); // Use minutes as hours
                        dt.setMinutes(+seconds); // Use seconds as minutes
                        return dt.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    }

                    // If the distance is less than or equal to 2km from the location marker, create a marker and add it to the map
                    if (distance <= 2) {

                        const popup = new mapboxgl.Popup({ offset: 25 })
                            .setHTML(`
                                <h2>${atm.Name}</h2>
                                <p>Location: ${atm.Location}</p>
                                
                                <p>${(atm.Close_Hours === '00:00:00' || atm.Close_Hours === null || atm.Start_Hours === '00:00:00') ? 'Open Availability' 
                                    : `Open Hours: ${formatTime12Hour(atm.Start_Hours)}
                                     - ${formatTime12Hour(atm.Close_Hours)}`}
                                </p>

                                <p>Latitude: ${atm.Latitude}</p>
                                <p>Longitude: ${atm.Longitude}</p>
                            `);

                        const marker = new mapboxgl.Marker()
                            .setLngLat([atmLongitude, atmLatitude])
                            .setPopup(popup)
                            .addTo(map);
                        currentMarkers.push(marker);
                    }
                });
            })
            .catch(error => console.error('Error:', error));
        }

        // Moves the map to the location and fetches markers
        function goToAddress() {
            const address = document.getElementById('addressInput').value;
            const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(address)}.json?access_token=${mapboxgl.accessToken}`;

            // Delete all previous markers
            currentMarkers.forEach(marker => marker.remove());
            currentMarkers = [];

            // Fetches the coordinates of the address
            fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.features && data.features.length > 0) {

                    // Get the coordinates of the first result
                    const coordinates = data.features[0].geometry.coordinates;

                    // Separates coordinates into longitude and latitude
                    const longitude = coordinates[0];
                    const latitude = coordinates[1];

                    // Debugging
                    console.log("Debug Coords: " + coordinates);
                    console.log("Debug lat and lon: " + longitude + ", " + latitude);
                    
                    // Move the map to the coordinates
                    map.flyTo({
                        center: coordinates,
                        zoom: 15
                    });

                    // Create and add a red marker for the searched location
                    const locationMarker = new mapboxgl.Marker({ color: 'red' })
                        .setLngLat(coordinates)
                        .setPopup(new mapboxgl.Popup().setHTML("You're here!"))
                        .addTo(map);
                    currentMarkers.push(locationMarker);

                    // Send coordinates to PHP for processing and then fetch markers
                    sendCoordinatesToPHP({longitude, latitude})
                        .then(() => {
                            // Fetch new markers based on the address
                            return fetchMarkers(longitude, latitude);
                        })
                        .catch(error => console.error('sendCoordinatesToPHP Error:', error));
                } else {
                    alert('Address not found.');
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }




    </script>

<!-- Address Button -->
    <style>
        .center-div {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: 100vh;
            margin: 0;
        }

        #addressInput, button {
            margin-top: 40px;
        }
    </style>

    <div class="center-div">
        <input type="text" id="addressInput" placeholder="Enter an address">
        <button onclick="goToAddress()">Go</button>
    </div>


</body>
</html>
