<?php

	/**
 	* This page shows all current trafficjams, or trafficjams on a give date and time.
	 */
	// Configuration.
	require_once 'config.php';

	// Get the searchcriteria.
	$searchDate = $_POST['searchDate'];
	$searchTime = $_POST['searchTime'];

	// Pagination.
	$limit = isset($_POST['limit']) ? $_POST['limit'] : '10';
	$offset = isset($_POST['offset']) ? $_POST['offset'] : '0';

	/**
	 * There are 2 modes:
	 * - search: the user has given a date and time to search for historical data of trafficjams.
	 * - default: the user has omitted a date and time and request current, live trafficjam data.
	 */
	$mode = ($searchDate != '' && $searchTime != '') ? 'search' : 'default';

	// To store the result.
	$trafficInfoJson;

	// Search trafficjams on a given date and time.
	if ($mode == 'search') {

		// Format the date and time.
		$date = substr($searchDate, 6, 4).substr($searchDate, 3, 2).substr($searchDate, 0, 2);
		$time = str_replace(':', '', $searchTime).'00';

		// Call the API to fetch trafficjams info at given date and time.
		$trafficInfoJson = file_get_contents($_SERVER_API.$_API_DIR."/traffic_jams.php?date={$date}&time={$time}&limit={$limit}&offset={$offset}");
	} else {

		// Get current trafficjams info.
		$trafficInfoJson = file_get_contents($_SERVER_API.$_API_DIR."/traffic_jams.php?limit={$limit}&offset={$offset}");
	}

	// Decode json.
    $trafficInfo = json_decode($trafficInfoJson);
    $trafficJams = $trafficInfo->trafficjams;

?>

<html>
    <head>
        <title>Overzicht files</title>
		<link rel="shortcut icon" type="image/png" href="./favicon.ico"/>
		<link type='text/css' rel='stylesheet' href='./css/traffic.css'>
		<link type='text/css' rel='stylesheet' href='css/calendar.css'>
		<link type='text/css' rel='stylesheet' href='http://weareoutman.github.io/clockpicker/dist/jquery-clockpicker.min.css'>
		<script type='text/javascript' src='http://maps.googleapis.com/maps/api/js?key=<?php echo $_GOOGLE_API_KEY; ?>&libraries=places'></script>
		<script type='text/javascript' src='js/calendar_eu.js'></script>
	</head>
    <body onLoad="document.getElementById('searchDate').focus();">
	
		<form name='form' id='form' action='<?php echo $_SERVER_APP; ?>/' method='post'>
		<input type='hidden' name='id' id='id' value=''>
		<input type='hidden' name='limit' id='limit' value='<?php echo $limit; ?>'>
		<input type='hidden' name='offset' id='offset' value='<?php echo $offset; ?>'>

		<table style='position: absolute; top: 10px; left: 10px; border-spacing: 4px; padding: 0px;'>
		<tr>
			<td colspan='4'><label class='header'><?php echo $mode == 'default' ? 'Actueel file overzicht' : 'File overzicht op '.$searchDate.' om '.$searchTime; ?></label></td>
		</tr>
		</table>

		<table style='position: absolute; top: 50px; left: 10px; border-spacing: 4px; padding: 0px;'>
		<tr>
		<td>
		<table>
		<tr>
			<td colspan='3'><label class='label'>Laat de zoekvelden leeg voor actuele files</label></td>
		</tr>
		<tr>
			<td><label>Zoek files op datum (DD-MM-JJJJ): </label><td>
			<td><input type='text' size='10' maxlength='10' name='searchDate' id='searchDate' value='<?php echo $searchDate; ?>' onblur='document.getElementById("searchDate").style = "background-color: white;"'>&nbsp;
				<script type='text/javascript'>new tcal ({'formname': 'form', 'controlname': 'searchDate'});</script><td>
		</tr>
		<tr>
			<td><label>Zoek files op tijdstip (UU:MM): </label><td>
			<td><input type='text' size='10' maxlength='5' name='searchTime' id='searchTime' value='<?php echo $searchTime; ?>' onblur='document.getElementById("searchTime").style = "background-color: white;"'>&nbsp;
				<img style='width: 20px; height: 20px;' name='clockImg' id='clockImg' src='img/clock.png'><td>
		</tr>
		<tr>
			<td><label>Toon maximaal 10 files</label>
							<input type='checkbox' name='checklimit' id='checklimit' <?php echo $limit == 10 ? 'checked' : '' ?>></td>
			<td colspan='2'><input type='button' name='search' id='search' value='Zoeken' class='button' onClick='goSearch();'>
							<input type='button' name='erase' id='erase' value='Wissen' class='button' onClick='eraseSearchFields();'></td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		</table>

		<table style='position: absolute; top: 125px; left: 10px;'>
		<tr>
			<td><label class='label'><?php echo sizeof($trafficJams); ?> file(s) gevonden.</label></td>
			<td>
				<label style='background-color: yellow;'>&nbsp;1 - 5&nbsp;</label>
				<label style='background-color: orange;'>&nbsp;6 - 15&nbsp;</label>
				<label style='background-color: red;'>&nbsp;> 15&nbsp;</label>
				<label class='label'><?php if (sizeof($trafficJams) > 10) echo 'Let op: alleen de eerste 10 files worden getoond!'; ?></label>
			</td>
		</tr>
		<tr>
			<td valign='top'>
				<table style='border-spacing: 4px; padding: 0px; border: 1px solid black; border-spacing: 0px; padding: 1px;'>
				<tr>
					<td><label class='messageTitle'>Weg</label></td><td>&nbsp;&nbsp;&nbsp;</td>
					<td><label class='messageTitle'>Van</label></td><td>&nbsp;&nbsp;&nbsp;</td>
					<td><label class='messageTitle'>Naar</label></td><td>&nbsp;&nbsp;&nbsp;</td>
					<td><label class='messageTitle'>Begon</label></td><td>&nbsp;&nbsp;&nbsp;</td>
					<td><label class='messageTitle'>Vertraging</label></td><td>&nbsp;&nbsp;&nbsp;</td>
					<td><label class='messageTitle'>Lengte</label></td><td>&nbsp;&nbsp;&nbsp;</td>
					<td><label class='messageTitle'>Metingen</label></td><td>&nbsp;&nbsp;&nbsp;</td>
				</tr>

<?php

	// Rows will alternative between white and gray backgrounds.
	$switch = 0;

	if ($trafficJams != null) {

		foreach($trafficJams as $trafficJam) {

			if ($switch % 2 == 0) {

				echo "<tr class='overview_item_gray' onClick='showJam({$trafficJam->id});'>";
			} else {

				echo "<tr class='overview_item_white' onClick='showJam({$trafficJam->id});'>";
			}

			echo "<td nowrap><label>{$trafficJam->roadName}</label></td><td>&nbsp;&nbsp;&nbsp;</td>";
			echo "<td nowrap><label>{$trafficJam->from}</label></td><td>&nbsp;&nbsp;&nbsp;</td>";
			echo "<td nowrap><label>{$trafficJam->to}</label></td><td>&nbsp;&nbsp;&nbsp;</td>";
			echo "<td nowrap><label>".substr($trafficJam->start, 11, 5)."</label></td><td>&nbsp;&nbsp;&nbsp;</td>";
			echo "<td nowrap><label>".($trafficJam->delay / 60)." min</label></td><td>&nbsp;&nbsp;&nbsp;</td>";
			echo "<td nowrap><label>".($trafficJam->distance / 1000.0)." km</label></td><td>&nbsp;&nbsp;&nbsp;</td>";
			echo "<td nowrap><label>{$trafficJam->segments}</label></td><td>&nbsp;&nbsp;&nbsp;</td>";
			echo '</tr>';

			$switch++;
		}
	}

	// No trafficjams found, show message.
	if ($switch == 0) {

		echo "<tr><td nowrap colspan='12'><label>Huh? Geen files in Nederland?! :-)</td></tr>";
	}
?>

				</table>
			</td>
			<td valign='top' rowspan='2'><table><tr><td><div style='width: 600px; height: 600px;' id='map-canvas'></div></td></tr></table></td>
		</tr>

<?php

	//Pagination.
	if (sizeof($trafficJams) >= $limit || $offset > 0) {

		echo "<tr style='height: 100%;'><td valign='top'>";
		if ($offset > 0) echo "<input class='button' type='button' value='Vorige' name='previous' id='previous' onClick='previousPage();'>";
		if (sizeof($trafficJams) >= $limit) echo "<input class='button' type='button' value='Volgende' name='next' id='next' onClick='nextPage();'>";
		echo "</td></tr>";
	}

?>

		</table>
		</form>

		<script type='text/javascript' src='//code.jquery.com/jquery-1.11.0.js'></script>
		<script type='text/javascript' src='http://weareoutman.github.io/clockpicker/dist/jquery-clockpicker.min.js'></script>
		<script type='text/javascript' src='js/clock.js'></script>
		<script type='text/javascript' src='js/functions.js'></script>
		<script type='text/javascript'>

			/***
			* Go to the details of the selected trafficjam.
			*/
			function showJam(id) {

				if (id == undefined) {

					alert('Nog geen historische data van deze file aanwezig.');
				} else {

					document.getElementById('id').value = id;
					document.getElementById('form').action = '<?php echo $_SERVER_APP; ?>/details.php';
					document.getElementById('form').submit();
				}
			}

			// Store the gps coordinates in an array to draw the routes on the map.
			var gps = [];

<?php

	// Loop over the trafficjams to get the gps coordinates and delay.
	if ($trafficJams != null) {

		$i = 0;
		foreach($trafficJams as $trafficJam) {

			echo "gps[{$i}] = {fromLocLat:'{$trafficJam->fromLocLat}', fromLocLon:'{$trafficJam->fromLocLon}', toLocLat:'{$trafficJam->toLocLat}', toLocLon:'{$trafficJam->toLocLon}', delay:'".($trafficJam->delay / 60)."'};";

			// Maximum of 10 routes will be drawn (limit of Google Maps api)
			if ($i++ == 10) break;
		}
	}

?>

		</script>
        <script type='text/javascript' src='js/map.js'></script>
    </body>
</html>