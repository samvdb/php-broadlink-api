<?php
require_once("include/broadlink.class.php");


$result = array();

$devices = Broadlink::Discover();

foreach ($devices as $device) {

	$obj = array();

	$obj['devtype'] = $device->devtype();
	$obj['name'] = $device->name();
	$obj['mac'] = $device->mac();
	$obj['host'] = $device->host();
	$obj['model'] = $device->model();

	if($obj['model'] == "RM2"){

		$device->Auth();
		$temperature = $device->Check_temperature();
		$obj['temperature'] = $temperature;
	}
	else if($obj['model'] == "A1"){

		$device->Auth();
		$data = $device->Check_sensors();

		$obj = array_merge($obj, $data);

	}




	array_push($result, $obj);
}

echo json_encode($result);
?>
