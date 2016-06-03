<?php

include("config.php");

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
			$telem =  htmlspecialchars($_POST["telemetry"]);
			$telem_file = fopen($config["telem_file"], 'a');
			fwrite($telem_file, $telem."\n");
			fclose($telem_file);
		}
	}
}

?>


