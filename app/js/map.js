// Create the map, set the default zoom and center the map on Utrecht.
var mapOptions = {
    zoom: 8,
    center: new google.maps.LatLng(52.09083, 5.12222),
    disableDefaultUI: false
};

// Needed to calculate the map bounds.
var bounds = new google.maps.LatLngBounds();

// To draw the routes.
var directionsService = new google.maps.DirectionsService();

// The map on which the routes will be drawn.
var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

// The delays are put into an array so they can later be retrieved when drawing a route (color is determined by delay time)
var delays = [];

// Loop over the trafficjams.
for (var i = 0; i < gps.length; i++) {

    // Get the GPS coordinates.
    var fromLocLat = gps[i].fromLocLat;
    var fromLocLon = gps[i].fromLocLon;
    var toLocLat = gps[i].toLocLat;
    var toLocLon = gps[i].toLocLon;
    var from = new google.maps.LatLng(fromLocLat, fromLocLon);
    var to = new google.maps.LatLng(toLocLat, toLocLon);

    // Put the delay in the array.
    delays[(fromLocLat.substr(0, fromLocLat.indexOf('.') + 3)) + (fromLocLon.substr(0, fromLocLon.indexOf('.') + 3)) + (toLocLat.substr(0, toLocLat.indexOf('.') + 3)) + (toLocLon.substr(0, toLocLon.indexOf('.') + 3))] = gps[i].delay;

    // Set the bounds.
    bounds.extend(from);
    bounds.extend(to);

    // Create the request.
    var request = {
        origin: from,
        destination: to,
        travelMode: 'DRIVING'
    };

    // Call the api to draw the trafficjam.
    directionsService.route(request, function(result, status) {

        if (result != null) {
            // Get the delay of this trafficjam.
            var delay = delays[((''+result.routes[0].legs[0].start_location.lat()).substr(0, (''+result.routes[0].legs[0].start_location.lat()).indexOf('.') + 3)) + ((''+result.routes[0].legs[0].start_location.lng()).substr(0, (''+result.routes[0].legs[0].start_location.lng()).indexOf('.') + 3)) + ((''+result.routes[0].legs[0].end_location.lat()).substr(0, (''+result.routes[0].legs[0].end_location.lat()).indexOf('.') + 3)) + ((''+result.routes[0].legs[0].end_location.lng()).substr(0, (''+result.routes[0].legs[0].end_location.lng()).indexOf('.') + 3))];

            // Draw the route on the map.
            if (status == 'OK') {

                // Determine the color.
                var col = '#0080ff';
                if (delay >= 1 && delay <= 5) col = 'yellow';
                if (delay >= 6 && delay <= 15) col = 'orange';
                if (delay > 15) col = 'red';

                var directionsRenderer = new google.maps.DirectionsRenderer({preserveViewport: gps.length != 1, polylineOptions: {strokeColor: col, strokeWeight:10}});
                directionsRenderer.setMap(map);
                directionsRenderer.setOptions({suppressMarkers: true});
                directionsRenderer.setDirections(result);
            }
        }
    });
}

// If there is at least one route, fit the map bounds so that it is only as big as needed.
if (gps.length > 0) map.fitBounds(bounds);
    
    