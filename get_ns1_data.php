<?php 
	class TelemData {
		public $date;
		public $time;
		public $lat;
		public $lon;
		public $alt;
		public $baro;
		public $tin;
		public $tout;
		public $batt;
	}

	$telem_data = new TelemData();

	// get last telemetry data
	function get_data() {
		global $telem_data;

		$line = '';

		$f = fopen('telem-ns1.txt', 'r');
		$cursor = -1;

		fseek($f, $cursor, SEEK_END);
		$char = fgetc($f);

		/**
		 * Trim trailing newline chars of the file
		 */
		while ($char === "\n" || $char === "\r") {
		    fseek($f, $cursor--, SEEK_END);
		    $char = fgetc($f);
		}

		/**
		 * Read until the start of file or first newline char
		 */
		while ($char !== false && $char !== "\n" && $char !== "\r") {
		    /**
		     * Prepend the new char
		     */
		    $line = $char . $line;
		    fseek($f, $cursor--, SEEK_END);
		    $char = fgetc($f);
		}
		
		// get values
		$telem = explode(";", $line);
		$telem_data->date = $telem[0];
		$telem_data->time = $telem[1];
		$telem_data->lat = $telem[2];
		$telem_data->lon = $telem[3];
		$telem_data->alt = $telem[4];
		$telem_data->batt = $telem[5];
		$telem_data->tin = $telem[6];
		$telem_data->tout = $telem[7];
		$telem_data->baro = $telem[8];
		// fix coordinates
		if ($telem_data->lat[strlen($telem_data->lat)-1] == 'S')
			$telem_data->lat = substr("-".ltrim($telem_data->lat, "0"), 0 ,-1);
		else
			$telem_data->lat = substr(ltrim($telem_data->lat, "0"), 0, -1);
		if ($telem_data->lon[strlen($telem_data->lon)-1] == 'W')
			$telem_data->lon = substr("-".ltrim($telem_data->lon, "0"), 0, -1);
		else
			$telem_data->lon = substr(ltrim($telem_data->lon, "0"), 0, -1);

	}
	
	get_data();
	echo json_encode($telem_data);
?> 


