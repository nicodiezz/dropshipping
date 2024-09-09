<?php

session_start();

if(isset($_SESSION['ID'])){
	require '../db.php';
	$db->query("INSERT INTO `pd_horarios` (`vendedorID`) VALUES ({$_SESSION['ID']})");
	echo $db->insert_id();
	die;
}

?>