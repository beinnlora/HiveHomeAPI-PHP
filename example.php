<?php
/*example code - replace your credentials below with your hivehome details*/
	include ("class.hivehome.php");
	//create new HiveHome object, debugging false
	$hive = new HiveHome("hiveusername", "hivepw",false);
	print "Target Temperature: ".$hive->getTargetTemperature()."\n";
	print "Actual Temperature: ".$hive->getCurrentTemperature()."\n";
	print "Boiler Status (heater): ".$hive->getHeaterState()."\n";
	
	
?>
