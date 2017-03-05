<?php 

include("config.php");

$m = new MongoClient();
$db = $m->selectDB('ashab-test');
$collection = new MongoCollection($db, 'data');

if (isset($_GET['id'])) 
{
	// get just the ID passed
	$query = array('_id' => $_GET['id']);
        $resp = $collection->findOne($query);
	echo json_encode($resp);
}

elseif (isset($_GET['alt'])) 
{
	// get alt data (array)
	$cursor = $collection->find(array(), array('alt'=>true));
	$resp = array();
	foreach ($cursor as $data) { 
		$resp[] = $data['alt'];
	}
	echo json_encode($resp);

}
elseif (isset($_GET['last']))
{
	// order by id (timestamp) and get just one
	$cursor = $collection->find()->sort(array('_id' => -1))->limit(1);
	foreach ($cursor as $data) {
		$json = json_encode($data);
	}
	

	$telem_data = json_decode($json);

	// convert coordinates (+/-)
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
	// get all data as an array of arrays
	$cursor = $collection->find();
	$resp = array();
	foreach ($cursor as $data) {
		$resp[] = $data;
	}
	echo json_encode($resp);
}

?> 


