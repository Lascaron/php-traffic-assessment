<?php

    /**
    * This page shows the historical data of one trafficjam.
    */
    
	// Configuration.
	require_once 'config.php';

    // Id of the selected trafficjam.
    $id = $_POST['id'];

    // These properties are kept in case the user returns to the searchpage.
    $searchDate = $_POST['searchDate'];
    $searchTime = $_POST['searchTime'];
    $limit = $_POST['limit'];
    $offset = $_POST['offset'];

    // Get trafficjam info from the api.
    $trafficInfoJson = file_get_contents($_SERVER_API.$_API_DIR."/traffic_jams.php?id={$id}");

    // Decode json.
    $trafficInfo = json_decode($trafficInfoJson);
    $trafficJams = $trafficInfo->trafficjams;
    $trafficJam = $trafficJams[0];

    // General properties of trafficjam. Used to show the road, from, to and map on the page.
    $roadName = $trafficJam->roadName;
    $from = $trafficJam->from;
    $fromLocLat = $trafficJam->fromLocLat;
    $fromLocLon = $trafficJam->fromLocLon;
    $to = $trafficJam->to;
    $toLocLat = $trafficJam->toLocLat;
    $toLocLon = $trafficJam->toLocLon;

?>

<html>
    <head>
        <title>File van <?php echo $roadName.' - '.$from; ?> naar <?php echo $to; ?></title>
		<link type='text/css' rel='stylesheet' href='./css/traffic.css'>
        <link rel="shortcut icon" type="image/png" href="./favicon.ico"/>
		<script type='text/javascript' src='http://maps.googleapis.com/maps/api/js?key=<?php echo $_GOOGLE_API_KEY; ?>&libraries=places'></script>
        <script type='text/javascript'>        
            
            // Variables needed to draw the map and the graphs.
            var api_url = '<?php echo $_SERVER_APP.$_API_DIR.'/traffic_jams.php?id='.$id; ?>';
            var gps = [];
            <?php echo "gps[0] = {fromLocLat:'{$fromLocLat}', fromLocLon:'{$fromLocLon}', toLocLat:'{$toLocLat}', toLocLon:'{$toLocLon}', delay:'0'};"; ?>
            var zeroGraphs = false;

            // Show graphs with offset 0, or not.
            function toggleGraphs() {
                
                zeroGraphs = !zeroGraphs;
                loadGraphs(zeroGraphs);
            }

        </script>
    </head>
    <body>
        <form name='form' id='form' action='<?php echo $_SERVER_APP; ?>/' method='post'>
        <input type='hidden' name='searchDate' id='searchDate' value='<?php echo $searchDate; ?>'>
        <input type='hidden' name='searchTime' id='searchTime' value='<?php echo $searchTime; ?>'>
        <input type='hidden' name='limit' id='limit' value='<?php echo $limit; ?>'>
        <input type='hidden' name='offset' id='offset' value='<?php echo $offset; ?>'>

        <table>
        <tr>
            <td colspan='2'><label class='header'>Overzicht file op de <?php echo $roadName; ?>, van <?php echo $from; ?> naar <?php echo $to; ?></label></td>
        </tr>
        <tr>
            <td><div id='chart'><canvas id='canvas'></canvas></div></td>
            <td rowspan='2'><div style='width: 600px; height: 600px;' id='map-canvas'></div></td>
        </tr>
        <tr>
            <td><div id='chart2'><canvas id='canvas2'></canvas></div></td>
        </tr>
        <tr>
            <td><label class='label'>Toon grafieken vanaf 0</label><input type='checkbox' onClick='toggleGraphs();'></td>
            <td style='text-align: right;'><input class='button' type='submit' value='Terug'></td>
        </tr>
        </table>

		<script type='text/javascript' src='//code.jquery.com/jquery-1.11.0.js'></script>
        <script type='text/javascript' src='js/Chart.min.js'></script>
        <script type='text/javascript' src='js/app.js'></script>
        <script type='text/javascript' src='js/map.js'></script>
    </body>
</html>