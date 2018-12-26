<?php

    /**
     * REST API.
     * This can be used to fetch:
     * - live trafficjams from ANWB
     * - trafficjams at a certain point in time
     * - data about one particular trafficjam (including historical data)
     */

    // Headers.
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=UTF-8');

	// Configuration.
	require_once 'config.php';

    // Database.
    require_once $_SHARED_DIR.'/database.php';

    // TrafficJam object.
    require_once $_SHARED_DIR.'/trafficjam.php';

    // Get database connection.
    $database = new Database();
    $db = $database->getConnection();

    // Create object.
    $trafficJam = new TrafficJam($db);

    // GET properties.
    // Id of a certain trafficjam.
    $id = $_GET['id'];

    // Searchcriteria.
    $date = $_GET['date'];
    $time = $_GET['time'];

    // Maximum results per page (user can overrule the default of 100 by 10)
    $limit = isset($_GET['limit']) ? $_GET['limit'] : '10';

    // Pagination
    $offset = isset($_GET['offset']) ? $_GET['offset'] : '0';

    /**
     * There are 3 modes:
     * - search: fetch trafficjams from the database on a given date and time.
     * - current: fetch live trafficjams from the ANWB API.
     * - details: fetch details/historical data from one trafficjam. 
     * Note: for simplicity, the mode is determined by GET parameters.
     */
    $mode = ($id != '' ? 'details' : ($date != '' && $time != '' ? 'search' : 'current'));

    // Array used to store trafficjam data.
    $trafficJams = array();
    $trafficJams['trafficjams'] = array();

    // Fetch the live trafficjams from the ANWB API.
    if ($mode == 'current') {

        // Call API.
        $trafficInfoJson = file_get_contents($_ANWB_API);

        // Decode json.
        $trafficInfo = json_decode($trafficInfoJson);

        // Keep track of number of trafficjams.
        $count = 0;

        // Loop over roads.
        foreach ($trafficInfo->roadEntries as $road) {

            // Roadname.
            $roadName = $road->road;

            // Loop over trafficjams.
            foreach ($road->events->trafficJams as $tj) {

                $count++;
                if ($count > $offset) {
                    // If there is no start date, delay, distance, the road is closed due to roadworks.
                    if ($tj->start == null || $tj->delay == null || $tj->distance == null) continue;

                    /**
                     * Since the data is fetched from the ANWB API, there is no information about historical data.
                     * Try to find an id of a record in the database about the same trafficjam so that can be used to show details of the trafficjam.
                     * If no record of the same trafficjam is found, the trafficjam has probably just started and is not yet saved to the database (by the cronjob)
                     */
                    $trafficJam->setFrom($tj->from);
                    $trafficJam->setTo($tj->to);
                    $trafficJam->setStart(str_replace('T', ' ', $tj->start));
                    $stmt = $trafficJam->findId();
                    $num = $stmt->rowCount();

                    // Check if an id is found.
                    $dataId;
                    if ($num > 0) {
            
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        extract($row);

                        $dataId = $id;
                        $dataSegments = $segments;
                    }

                    // Create entry for the json result.
                    $traffic_jam = array('from' => $tj->from,
                                        'fromLocLat' => $tj->fromLoc->lat,
                                        'fromLocLon' => $tj->fromLoc->lon,
                                        'to' => $tj->to,
                                        'toLocLat' => $tj->toLoc->lat,
                                        'toLocLon' => $tj->toLoc->lon,
                                        'start' => $tj->start,
                                        'delay' => $tj->delay,
                                        'distance' => $tj->distance,
                                        'roadName' => $roadName,
                                        'id' => $dataId,
                                        'segments' => $dataSegments
                    );

                    // As long as the maximum requested is not reached, add the entry to the resultset.
                    if (sizeof($trafficJams['trafficjams']) == $limit) break;
                    array_push($trafficJams['trafficjams'], $traffic_jam);
                }
            }
        }

    // Fetch all information about 1 trafficjam.
    } else if ($mode == 'details') {

        // Set the id.
        $trafficJam->setId($id);
    
        // Fetch data.
        $stmt = $trafficJam->select();
        $num = $stmt->rowCount();
    
        // Data is found.
        if ($num > 0) {
        
            // Eventhough 1 trafficjam is requested, the result can be many since they are measurements of that trafficjam.
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                extract($row);
    
                // Create entry for the json result.
                $traffic_jam = array('id' => $id,
                                     'from' => html_entity_decode($from),
                                     'fromLocLat' => html_entity_decode($from_loc_lat),
                                     'fromLocLon' => html_entity_decode($from_loc_lon),
                                     'to' => html_entity_decode($to),
                                     'toLocLat' => html_entity_decode($to_loc_lat),
                                     'toLocLon' => html_entity_decode($to_loc_lon),
                                     'start' => html_entity_decode($start),
                                     'delay' => html_entity_decode($delay),
                                     'distance' => html_entity_decode($distance),
                                     'timestamp' => html_entity_decode($timestamp),
                                     'roadName' => html_entity_decode($road_name)
                );
    
                // Add entry to the resultset.
                array_push($trafficJams['trafficjams'], $traffic_jam);
            }
        }

    // Search for trafficjams with the given searchcriteria.
    } else if ($mode == 'search') {

        // Search trafficjams.
        $stmt = $trafficJam->selectMultiple($date.$time, $limit, $offset);
        $num = $stmt->rowCount();

        // Data is found.
        if ($num > 0) {
        
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                extract($row);

                // Create entry for the json result.
                $traffic_jam = array('id' => $id,
                                     'from' => html_entity_decode($from),
                                     'fromLocLat' => html_entity_decode($from_loc_lat),
                                     'fromLocLon' => html_entity_decode($from_loc_lon),
                                     'to' => html_entity_decode($to),
                                     'toLocLat' => html_entity_decode($to_loc_lat),
                                     'toLocLon' => html_entity_decode($to_loc_lon),
                                     'start' => html_entity_decode($start),
                                     'delay' => html_entity_decode($delay),
                                     'distance' => html_entity_decode($distance),
                                     'timestamp' => html_entity_decode($timestamp),
                                     'roadName' => html_entity_decode($road_name),
                                     'segments' => html_entity_decode($segments),
                );

                // Add entry to the resultset.
                array_push($trafficJams['trafficjams'], $traffic_jam);
            }
        }
    }

    // Response 200 OK.
    http_response_code(200);

    // Return results.
    if (sizeof($trafficJams['trafficjams']) > 0) {

        // Show trafficjam in json format
        echo json_encode($trafficJams);
    } else {

        // No data found.
        echo json_encode(
            array('message' => 'No trafficjams found.')
        );
    }

?>
 