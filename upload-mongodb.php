<?php

include("config.php");

//error_reporting(E_ALL);
//ini_set('display_errors', 'on');

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
		//if ($passwords[$username] != crypt($password, $passwords[$username])) {
		if (!password_verify($password, $passwords[$username])) {
			header('WWW-Authenticate: Basic realm="ASHAB"');
	                header('HTTP/1.0 401 Unauthorized');
	                echo 'You shall not pass'."\n";
        	        die();
		} else {
			echo "You can pass. ";

			// check for image
			if ($_POST["image"] != "") {
				echo "Detected Image.\n";
				echo $_POST["image"]."\n";
				
				// get paths and meta
				$target_dir = $config["ssdv_dir"];
				$target_file = $target_dir . basename($_FILES["uploaded"]["name"]);
				echo "TARGET:".$target_file."\n";
				$imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
				
				// Check file size
				if ($_FILES["uploaded"]["size"] > 500000) {
				    echo "Sorry, your file is too large.";
				    die();
				}

				// file type
				if ($imageFileType != "jpg") {
					echo "Sorry, only JPG";
					die();
				}
				
				// ok, save it
				if (move_uploaded_file($_FILES["uploaded"]["tmp_name"], $target_file)) {
				        echo "The file ". basename( $_FILES["uploaded"]["name"]). " has been uploaded.\n";
			        } else {
				        echo "Sorry, there was an error uploading your file.\n";
				}
				
				// update last image
				copy ($target_file, $config["ssdv_dir"]."last.jpg");	

			} elseif ($_POST["telemetry"] != "") {

				// get headers
				$telem_header =  htmlspecialchars($_POST["telemetry"]);
				$database_header = htmlspecialchars($_POST["database"]);			
				
				// get data
				$telem = explode(";", $telem_header);

				// create id
				$microtime_list = explode(" ", microtime());
				$microseconds_float = $microtime_list[0];
				$microseconds_list = explode(".", $microseconds_float);
				$microseconds = $microseconds_list[1];
				$timestamp = "" . time() . $microseconds;
					
				$telem_data = array(
					'_id' => $timestamp,
					'date' => $telem[0],
					'time' => $telem[1],
					'lat' => $telem[2],
					'lon' => $telem[3],
					'alt' => $telem[4],
					'batt' => $telem[5],
					'tin' => $telem[6],
					'tout' => $telem[7],
					'baro' => $telem[8],
					'hdg' => $telem[9],
					'spd' => $telem[10],
					'sats' => $telem[11],
					'a_rate' => $telem[12]
				);
				
				$dbhost = $config["mongo_host"]; 

				$m = new Mongo("mongodb://$dbhost");
				$db = $m->selectDB($database_header);
			

				// insert data
				$coll = $db->data;
				$coll->save($telem_data);

				echo 'Telemetry inserted. ';
			}	
		}
	}
}

?>


