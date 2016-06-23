<?php

include("config.php");
include("couchdb.php");

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


$username = null;
$password = null;

// mod_php
if (isset($_SERVER['PHP_AUTH_USER'])) {
	$username = $_SERVER['PHP_AUTH_USER'];
	$password = $_SERVER['PHP_AUTH_PW'];

// most other servers
} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {

	if (strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']),'basic')===0)
		list($username,$password) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));

}

// no username
if (is_null($username)) {

	header('WWW-Authenticate: Basic realm="ASHAB"');
	header('HTTP/1.0 401 Unauthorized');
	echo 'You shall not pass';

	die();

} else {
	include("track_pass.php");
	// check username
	if (!array_key_exists($username, $passwords)) {
		header('WWW-Authenticate: Basic realm="ASHAB"');
	        header('HTTP/1.0 401 Unauthorized');
	        echo 'You shall not pass';
        	die();
	} else {
	// check password
		if ($passwords[$username] != crypt($password, $passwords[$username])) {
			header('WWW-Authenticate: Basic realm="ASHAB"');
	                header('HTTP/1.0 401 Unauthorized');
	                echo 'You shall not pass'."\n";
        	        die();
		} else {
			echo "You can pass.\n";

			// get headers
			$telem_header =  htmlspecialchars($_POST["telemetry"]);
			$database_header = htmlspecialchars($_POST["database"]);			
			
			// get data
			$telem_data = new TelemData();
			$telem = explode(";", $telem_header);
			$telem_data->date = $telem[0];
			$telem_data->time = $telem[1];
			$telem_data->lat = $telem[2];
			$telem_data->lon = $telem[3];
			$telem_data->alt = $telem[4];
			$telem_data->batt = $telem[5];
			$telem_data->tin = $telem[6];
			$telem_data->tout = $telem[7];
			$telem_data->baro = $telem[8];
			$telem_data->hdg = $telem[9];
			$telem_data->spd = $telem[10];
			
			// insert data in couchdb
			$couchdb_options['host'] = $config["couchdb_host"]; 
			$couchdb_options['port'] = $config["couchdb_port"];

			$couch = new CouchSimple($couchdb_options); // See if we can make a connection
			// try to create a new database 
			$resp = $couch->send("PUT", $database_header);
			// insert data
			$microtime_list = explode(" ", microtime());
			$microseconds_float = $microtime_list[0];
			$microseconds_list = explode(".", $microseconds_float);
			$microseconds = $microseconds_list[1];
			$timestamp = "" . time() . $microseconds;
			$resp = $couch->send("PUT", "/".$database_header."/".$timestamp, json_encode($telem_data));
			echo var_dump($resp);	
		
		}
	}
}

?>


