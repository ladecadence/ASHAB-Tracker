<?php 
	include("config.php");
?>

<!DOCTYPE HTML>
<html>
  <head>
    <title>ASHAB NS1 Tracker</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="tracker.css" />
    <link href="res/lightbox.css" rel="stylesheet">
    <script src="res/lightbox-plus-jquery.min.js"></script>
    <script src="http://openlayers.org/api/OpenLayers.js"></script>    
    <script src="res/Chart.js"></script>
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
		// configure options
		Chart.defaults.global.elements.point.radius = 1;
		Chart.defaults.global.title.fontColor = "#eee";
		Chart.defaults.global.animation.duration = 0;
		Chart.defaults.global.legend.display = false;
		
		// get telemetry data
		var request = OpenLayers.Request.GET({
			url: "<?php echo $config["get_data_url"]; ?>?last" ,
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
		
		icon = new OpenLayers.Icon("<?php echo $config["payload_icon"]; ?>", new OpenLayers.Size(27,30));
		arrow_icon = new OpenLayers.Icon("<?php echo $config["arrow_icon"]; ?>", new OpenLayers.Size(82,82));
		
		if (telem_data.a_rate < -1) {
			ascent_descent_icon = new OpenLayers.Icon("<?php echo $config["parachute_icon"]; ?>", 
						new OpenLayers.Size(220,220));
		}
		else {
			ascent_descent_icon = new OpenLayers.Icon("<?php echo $config["balloon_icon"]; ?>", 
                                                new OpenLayers.Size(220,220));
		}
		
		arrow_marker_div = arrow_icon.imageDiv;
		rotateAnimation(arrow_marker_div, telem_data.hdg);
		
            	payload_marker = new OpenLayers.Marker(lonLat, icon);
		arrow_marker = new OpenLayers.Marker(lonLat, arrow_icon);
        	balloon_marker = new OpenLayers.Marker(lonLat, ascent_descent_icon);    
		
		markers.addMarker(payload_marker);	
		markers.addMarker(balloon_marker);	
		markers.addMarker(arrow_marker);
		
 
		map.setCenter (lonLat, 12);

		generate_status_content();
		update_graphs();
		update_ssdv();
  	}
	
	// updates the position of the capsule
	function update() {
		// get telemetry data
		var request = OpenLayers.Request.GET({
			url: "<?php echo $config["get_data_url"]; ?>?last",
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

		if (telem_data.a_rate < -1) {
			ascent_descent_icon = new OpenLayers.Icon("<?php echo $config["parachute_icon"]; ?>", 
						new OpenLayers.Size(220,220));
		}
		else {
			ascent_descent_icon = new OpenLayers.Icon("<?php echo $config["balloon_icon"]; ?>", 
                                                new OpenLayers.Size(220,220));
		}
		
		markers.removeMarker(balloon_marker);
		markers.removeMarker(arrow_marker);
		markers.removeMarker(payload_marker);

            	payload_marker = new OpenLayers.Marker(lonLat, icon);
		arrow_marker = new OpenLayers.Marker(lonLat, arrow_icon);
        	balloon_marker = new OpenLayers.Marker(lonLat, ascent_descent_icon);    

		arrow_marker_div = arrow_icon.imageDiv;
		rotateAnimation(arrow_marker_div, telem_data.hdg);

		markers.addMarker(payload_marker);	
            	markers.addMarker(balloon_marker);	
		markers.addMarker(arrow_marker);
		
		map.setCenter (lonLat);

		generate_status_content();
		update_graphs();
		update_ssdv();
	}
	
	// generates the status text
	function generate_status_content() {
		document.getElementById("status").innerHTML = "" +
			"<h2>NSX STATUS</h2>" +
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
			"<span>ASC. RATE:</span><br /> <strong>"+telem_data.a_rate+" m/s</strong><br />" +
			"<div id=\"ssdv\"></div>" +
			"<br />" + "<span>Locate in google maps</span><br />" + 
			"<a href=\"http://maps.google.com/maps?z=12&t=m&q=loc:"+ 
			telem_data.lat+"+"+telem_data.lon+"\">"+"LINK" + "</a>";
		
	}

	// updates the graph
	function update_graphs() {
		// get telemetry data
		var request = OpenLayers.Request.GET({
			url: "<?php echo $config["get_data_url"]; ?>?alt",
			callback: update_alt_chart 
		});
	}


	function update_alt_chart(request) {
		// get data
		var obj = request.responseText;					
		var alt_data = JSON.parse(obj);
		// ok, we should have an array
		var altitudes = [];
		var labels = [];
		//for (var i in alt_data.rows) {
		//	if (!isNaN(parseFloat(alt_data.rows[i].value)))
		//	{
		//		altitudes.push(parseFloat(alt_data.rows[i].value));
		//		labels.push(i.toString());
		//	}
		//}
		for (i=0; i<alt_data.length; i++) {
			altitudes.push(parseFloat(alt_data[i]));
			labels.push(i.toString());
		}
		console.log(altitudes);

		// create graph
		var ctx = document.getElementById("altChart");
		
		var altData = {
			labels : labels,
			datasets : [
				{
					label: "Altitude",
					backgroundColor : "rgba(238,238,238,1)",
					fill: true,
					strokeColor : "#eee",
					pointColor : "#eee",
					pointStrokeColor : "#9DB86D",
					lineTension : 0.4,
					pointRadius : 0,
					borderWidth : 1,
					borderColor: "#eee",
					data : altitudes
				}
			]
		}
		var altOptions = {
			responsive: true,
			scales: {
				type: "linear",
				xAxes: [{
					display: true,
					gridLines: {
						display: false
					},
					ticks: {
						fontSize: 8,
						display: false
					}
				}],
				yAxes: [{
					display: true,
					gridLines: {
						display: false
					},
					ticks: {
                                                fontSize: 8,
						fontColor: "#ccc",
						min: 0
                                        }
				}]				    	
			}
		};
		
		new Chart(ctx, {
			type: 'line',
			data: altData,
			options: altOptions
		});
		
		document.getElementById("lastAlt").innerHTML = "" + telem_data.alt + "m";
		
		


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

	function update_ssdv() {
		document.getElementById("ssdv").innerHTML = "" +
			"<span>LAST IMAGE:</span><br />" +
			"<a href='ssdv/last.jpg?"+ new Date().getTime() +"' data-lightbox='ssdv-1' data-title='Last SSDV Image'>"+
			"<img class='ssdv' src='ssdv/last.jpg?" + new Date().getTime() + "'/>" + 
			"</a>";
	}

	// reloads info each 30 seconds
	var reload = setInterval(update, 20000);
	var reload_graph = setInterval(update_graphs, 20000);
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
			<div id="alt-chart">
				<h2> ALTITUDE </h2>
				<span id="lastAlt"></span>
				<canvas id="altChart" width="150px" height="150px"></canvas>
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
