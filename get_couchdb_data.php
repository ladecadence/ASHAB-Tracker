<?php 

include("config.php");

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

class CouchSimple {
	function CouchSimple($options) {
		foreach($options AS $key => $value) {
			$this->$key = $value;
		}
	} 

	function send($method, $url, $post_data = NULL) {
		$s = fsockopen($this->host, $this->port, $errno, $errstr); 
		if(!$s) {
			echo "$errno: $errstr\n"; 
			return false;
		} 

		$request = "$method $url HTTP/1.0\r\nHost: $this->host\r\n"; 

		if ($this->user) {
			$request .= "Authorization: Basic ".base64_encode("$this->user:$this->pass")."\r\n"; 
		}

		if($post_data) {
			$request .= "Content-Length: ".strlen($post_data)."\r\n\r\n"; 
			$request .= "$post_data\r\n";
		} 
		else {
			$request .= "\r\n";
		}

		fwrite($s, $request); 
		$response = ""; 

		while(!feof($s)) {
			$response .= fgets($s);
		}

		list($this->headers, $this->body) = explode("\r\n\r\n", $response); 
		return $this->body;
	}
}


$telem_data = new TelemData();

$couch_options['host'] = "localhost";
$couch_options['port'] = 5984;

$couch = new CouchSimple($couch_options);

// get last ID
$resp = $couch->send("GET", "/".$config["database"]."/_view/get/data_ids?count=1&descending=true");
$json = json_decode($resp);

$last_id = $json->{'rows'}[0]->{'id'};

// ok, get all data
$resp = $couch->send("GET", "/".$config["database"]."/".$last_id);
$telem_data = json_decode($resp);

if ($telem_data->lat[strlen($telem_data->lat)-1] == 'S')
	$telem_data->lat = substr("-".ltrim($telem_data->lat, "0"), 0 ,-1);
else
	$telem_data->lat = substr(ltrim($telem_data->lat, "0"), 0, -1);

if ($telem_data->lon[strlen($telem_data->lon)-1] == 'W')
	$telem_data->lon = substr("-".ltrim($telem_data->lon, "0"), 0, -1);
else
	$telem_data->lon = substr(ltrim($telem_data->lon, "0"), 0, -1);

echo json_encode($telem_data);

?> 


