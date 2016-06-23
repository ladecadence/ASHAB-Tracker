<?php 

include("config.php");
include("couchdb.php");

$couch_options['host'] = $config["couchdb_host"];
$couch_options['port'] = $config["couchdb_port"];

$couch = new CouchSimple($couch_options);

if (isset($_GET['id'])) 
{
	// get ID
        $resp = $couch->send("GET", "/".$config["database"]."/".$_GET['id']);
        echo $resp;
}
elseif (isset($_GET['alt'])) 
{
	// get alt data
	$resp = $couch->send("GET", "/".$config["database"]."/_view/get/alt");
        echo $resp;
}
elseif (isset($_GET['last']))
{
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
}
else
{
	// get all data
	$resp = $couch->send("GET", "/".$config["database"]."/_view/get/all_data");
	echo $resp;
}

?> 


