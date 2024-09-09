<?php
//process nearby_
	//get lat, lon
	//use algo to get nearbies
	//if 0, increase until 1+ are found
	// $_SESSION['nearby']=[];
	session_start();
	$datos=json_decode(file_get_contents('php://input'),true);
	$_SESSION['clientLocation']=[$datos['lat'],$datos['lon']];
	$_SESSION['next']=time()+3600*24*7;//reintentar en 7 dias
	//return(?) last distance used
	die('15');//define starting width, focus on Santa Fe
?>