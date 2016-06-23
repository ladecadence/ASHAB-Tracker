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
	var arrow_icon;
	var balloon_marker;
	var arrow_marker;
	var arrow_marker_div;
	var telem_data;

	function rotateAnimation(elem,degrees){
		//var elem = document.getElementById(el);
		if(navigator.userAgent.match("Chrome")){
			elem.style.WebkitTransform = "rotate("+degrees+"deg)";
		} else if(navigator.userAgent.match("Firefox")){
			elem.style.MozTransform = "rotate("+degrees+"deg)";
		} else if(navigator.userAgent.match("MSIE")){
			elem.style.msTransform = "rotate("+degrees+"deg)";
		} else if(navigator.userAgent.match("Opera")){
			elem.style.OTransform = "rotate("+degrees+"deg)";
		} else {
			elem.style.transform = "rotate("+degrees+"deg)";
		}
		if(degrees > 359){
			degrees = 1;
		}
	}	

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
		arrow_icon = new OpenLayers.Icon("<?php echo $config["arrow_icon"]; ?>", new OpenLayers.Size(82,82));
		
		arrow_marker_div = arrow_icon.imageDiv;
		rotateAnimation(arrow_marker_div, 120);
		
            	balloon_marker = new OpenLayers.Marker(lonLat, icon);
		arrow_marker = new OpenLayers.Marker(lonLat, arrow_icon);
            	
		markers.addMarker(balloon_marker);	
		markers.addMarker(arrow_marker);
		
 
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
		icon = new OpenLayers.Icon("<?php echo $config["payload_icon"]; ?>", new OpenLayers.Size(27,30));
		arrow_icon = new OpenLayers.Icon("<?php echo $config["arrow_icon"]; ?>", new OpenLayers.Size(82,82));


		markers.removeMarker(balloon_marker);
		markers.removeMarker(arrow_marker);

            	balloon_marker = new OpenLayers.Marker(lonLat, icon);
		arrow_marker = new OpenLayers.Marker(lonLat, arrow_icon);

		arrow_marker_div = arrow_icon.imageDiv;
		rotateAnimation(arrow_marker_div, 120);

            	markers.addMarker(balloon_marker);	
		markers.addMarker(arrow_marker);
		
		map.setCenter (lonLat);

		generate_status_content();
		update_altimeter();
	}
	
	// generates the status text
	function generate_status_content() {
		document.getElementById("status").innerHTML = "" +
			"<h2>NS1 STATUS</h2>" +
			"<span>DATE:</span><br /> <strong>"+telem_data.date+"</strong><br />"+
			"<span>TIME:</span><br /> <strong>"+telem_data.time+"</strong><br />"+
			"<span>LATITUDE:</span><br /> <strong>"+telem_data.lat+"</strong><br />"+
			"<span>LONGITUDE:</span><br /> <strong>"+telem_data.lon+"</strong><br />"+
			"<span>ALTITUDE:</span><br /> <strong>"+telem_data.alt+" m</strong><br />"+
			"<span>BAROMETER:</span><br /> <strong>"+telem_data.baro+" mb</strong><br />"+
			"<span>INTERNAL TEMPERATURE:</span><br /> <strong>"+telem_data.tin+" ºC</strong><br />"+
			"<span>EXTERNAL TEMPERATURE:</span><br /> <strong>"+telem_data.tout+" ºC</strong><br />"+
			"<span>BATTERY:</span><br /> <strong>"+telem_data.batt+" V</strong><br />"+
			"<span>HEADING:</span><br /> <strong>"+telem_data.hdg+" º</strong><br />" +
			"<span>SPEED:</span><br /> <strong>"+telem_data.spd+" kn</strong><br />" +
			"<br />" + "<span>Locate in google maps</span><br />" + 
			"<a href=\"http://maps.google.com/maps?z=12&t=m&q=loc:"+ 
			telem_data.lat+"+"+telem_data.lon+"\">"+"LINK" + "</a>";
		
	}

	function update_altimeter() {
		document.getElementById("alt-ns1").style.bottom = ((telem_data.alt * 100) / 40000).toString()+"%";
	}
	
	function update_clock() {
		var date = new Date();
		document.getElementById("clock").innerHTML = "" +
			"<h2>TIME</h2><br />" +
			"<span>local</span></br><strong>" +
			pad(date.getHours(),2) + ":" +
			pad(date.getMinutes(),2) + ":" +
			pad(date.getSeconds(),2) + "</strong><br />" +
			"<span>utc</span></br><strong>" +
			pad(date.getUTCHours(),2) + ":" +
                        pad(date.getUTCMinutes(),2) + ":" +
                        pad(date.getUTCSeconds(),2)+ "</strong>";
	}

	// reloads info each 30 seconds
	var reload = setInterval(update, 20000);
	var clock = setInterval(update_clock, 1000);	

    </script>

  </head>
  <body onload="init();">
  	<div id="content">
     		<div id="left_column">
			<div id="status"></div>
    		</div>
 		<div id="right_column">
			<div id="logo" class="logo">
				<a href="http://ashab.space">
    					<img src="<?php echo $config["logo_img"]; ?>" alt="ASHAB" height="150px">
				</a>
    			</div>
			<div id="clock"></div>
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
		</div>
		<div id="middle_column">
			<div id="map"></div>
  		</div>
 	 </div>
	<div id="footer">
		&copy;2016 David Pello & ASHAB - Asturias High Altitude Ballooning<br />
		Source code at <a href="https://github.com/ladecadence/ASHAB-Tracker">GitHub</a><br />
		<a href="http://ashab.space">http://ashab.space</a>
	</div>
 </body>
</html>
