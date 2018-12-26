<?php

	/**
	 * This file does the following:
	 * 
	 * 1. Call ANWB API.
	 * 2. Transform JSON to object.
	 * 3. Get relevant information (traffic information) and put in TrafficJam objects.
	 * 4. Save each TrafficJam object to the database.
	 */

	// Configuration.
	require_once 'config.php';

	// Database connection.
    require_once $_SHARED_DIR.'/database.php';

	// TrafficJam object.
	require_once $_SHARED_DIR.'/trafficjam.php';

	// Setting up the database.
    $database = new Database();
    $db = $database->getConnection();

	// Call ANWB API.
	$trafficInfoJson = file_get_contents($_ANWB_API);

	// Decode json.
	$trafficInfo = json_decode($trafficInfoJson);

	// Get current timestamp and format from api source.
	$format = 'Ymd, H:i';
	$currentTimestamp = DateTime::createFromFormat($format, $trafficInfo->dateTime);

	// Loop over roads.
	foreach ($trafficInfo->roadEntries as $road) {

		// Roadname.
		$roadName = $road->road;

		// Loop over trafficjams.
		foreach ($road->events->trafficJams as $tj) {

			// If there is no start date, delay, distance, the road is closed due to roadworks.
			if ($tj->start == null || $tj->delay == null || $tj->distance == null) continue;

			// Create a TrafficJam object.
			$trafficJam = new TrafficJam($db);

			$trafficJam->setFrom($tj->from);
			$trafficJam->setFromLocLat($tj->fromLoc->lat);
			$trafficJam->setFromLocLon($tj->fromLoc->lon);
			$trafficJam->setTo($tj->to);
			$trafficJam->setToLocLat($tj->toLoc->lat);
			$trafficJam->setToLocLon($tj->toLoc->lon);
			$trafficJam->setStart(str_replace('T', ' ', $tj->start));
			$trafficJam->setDelay((int)$tj->delay);
			$trafficJam->setDistance((int)$tj->distance);
			$trafficJam->setTimestamp($currentTimestamp->format('Y-m-d H:i:s'));
			$trafficJam->setRoadName($roadName);

			// Save the TrafficJam object to the database.
			if ($trafficJam->insert() === FALSE) {

				echo 'Could not save to the database.<br/>';
			};
		}
	}

	// This can be used for testing purposes (so no cronjob is needed). Turn off in production!
	header('Refresh:300');

?>
