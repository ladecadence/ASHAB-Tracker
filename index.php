<?php 
	include("config.php");
?>

<!DOCTYPE HTML>
<html>
  <head>
    <title>ASHAB NS1 Tracker</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="tracker.css" />
    <script src="http://openlayers.org/api/OpenLayers.js"></script>    
   <script type="text/javascript">
	var map;
	var options;
	var layer;
	var lonLat;
	var markers;
	var icon;
	var balloon_marker;
	var telem_data;
	

	function pad (num, size) {
		var s = num+"";
		while (s.length < size) s = "0" + s;
		return s;
	}

	// Creates the map and markers
	function init() {
		// get telemetry data
		var request = OpenLayers.Request.GET({
			url: "<?php echo $config["get_data_url"]; ?>" ,
			callback: create_map 
		});
	}

	function create_map(request) {
		var obj = request.responseText;					
		telem_data = JSON.parse(obj);


		// create map
    		map = new OpenLayers.Map( 'map' );
		options = {
                    attribution: {
                        title: "Provided by ASHAB",
                        href: "http://ashab.space/"
                    }
                };
    		layer = new OpenLayers.Layer.OSM("OSM Map");
   		map.addLayer(layer);
    		lonLat = new OpenLayers.LonLat(telem_data.lon, 
				telem_data.lat).transform(
    				                new OpenLayers.Projection("EPSG:4326"),
                    				map.getProjectionObject()
                				);
    		map.addControl( new OpenLayers.Control.LayerSwitcher() );
	
		markers = new OpenLayers.Layer.Markers( "Markers" );
    		map.addLayer(markers);
		
		icon = new OpenLayers.Icon("<?php echo $config["payload_icon"]; ?>", new OpenLayers.Size(29,32));

            	balloon_marker = new OpenLayers.Marker(lonLat, icon);
            	markers.addMarker(balloon_marker);	
		
 
		map.setCenter (lonLat, 12);

		generate_status_content();
		update_altimeter();
  	}
	
	// updates the position of the capsule
	function update() {
		// get telemetry data
		var request = OpenLayers.Request.GET({
			url: "<?php echo $config["get_data_url"]; ?>",
			callback: update_map 
		});
	}
	
	function update_map(request) {
		var obj = request.responseText;					
		telem_data = JSON.parse(obj);


		lonLat = new OpenLayers.LonLat(telem_data.lon, 
                                		telem_data.lat).transform(
                                                new OpenLayers.Projection("EPSG:4326"),
                                                map.getProjectionObject()
                                                );
		icon = new OpenLayers.Icon("<?php echo $config["payload_icon"]; ?>", new OpenLayers.Size(29,32));

		markers.removeMarker(balloon_marker);
            	balloon_marker = new OpenLayers.Marker(lonLat, icon);
            	markers.addMarker(balloon_marker);	
		
		map.setCenter (lonLat);

		generate_status_content();
		update_altimeter();
	}
	
	// generates the status text
	function generate_status_content() {
		document.getElementById("status").innerHTML = "" +
			"<strong><ins>NS1 STATUS</ins></strong><br /><br />" +
			"Date: <strong>"+telem_data.date+"</strong><br />"+
			"Time: <strong>"+telem_data.time+"</strong><br />"+
			"Latitude: <strong>"+telem_data.lat+"</strong><br />"+
			"Longitude: <strong>"+telem_data.lon+"</strong><br />"+
			"Altitude: <strong>"+telem_data.alt+"</strong><br />"+
			"Baro: <strong>"+telem_data.baro+"</strong><br />"+
			"Temp. Internal: <strong>"+telem_data.tin+"</strong><br />"+
			"Temp. External: <strong>"+telem_data.tout+"</strong><br />"+
			"Battery: <strong>"+telem_data.batt+"</strong><br />"+
			"<a href=\"http://maps.google.com/maps?z=12&t=m&q=loc:"+ 
			telem_data.lat+"+"+telem_data.lon+"\">"+"GMaps" + "</a>";
		
	}

	function update_altimeter() {
		document.getElementById("alt-ns1").style.bottom = ((telem_data.alt * 100) / 40000).toString()+"%";
	}
	
	function update_clock() {
		var date = new Date();
		document.getElementById("clock").innerHTML = "" +
			"<strong><ins>TIME</ins></strong><br /><br />" +
			pad(date.getHours(),2) + ":" +
			pad(date.getMinutes(),2) + ":" +
			pad(date.getSeconds(),2) + " L<br />" +
			pad(date.getUTCHours(),2) + ":" +
                        pad(date.getUTCMinutes(),2) + ":" +
                        pad(date.getUTCSeconds(),2)+ " Z";
	}

	// reloads info each 30 seconds
	var reload = setInterval(update, 20000);
	var clock = setInterval(update_clock, 1000);	

    </script>

  </head>
  <body onload="init();">
    <div id="map"></div>
    <div id="clock"></div>
    <div id="logo" class="logo">
	<a href="http://ashab.space">
    		<img src="<?php echo $config["logo_img"]; ?>" alt="ASHAB" height="150px">
	</a>
    </div>
    <div id="status">
    </div>
    <div id="alt" class="alt">
	<div id="a35000m" class="a35000m">
		35000 m
	</div>

	<div id="a30000m" class="a30000m">
		30000 m
	</div>
	<div id="a25000m" class="a25000m">
		25000 m
	</div>

	<div id="a20000m" class="a20000m">
		20000 m
	</div>
	<div id="a15000m" class="a15000m">
		15000 m
	</div>

	<div id="a10000m" class="a10000m">
		10000 m
	</div>
	<div id="a5000m" class="a5000m">
		5000 m
	</div>

	<div id="a0m" class="a0m">
		0 m
	</div>
	<div id="alt-ns1" class="alt-ns1">
		<img src="<?php echo $config["payload_icon"]; ?>" alt="ASHAB" >
	</div>

    </div>
  </body>
</html>
