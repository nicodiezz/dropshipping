<?php

session_start();
if(!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin'])
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

$delivery=(float)(json_decode(file_get_contents('php://input'),true)['delivery']);
require '../db.php';

$db->query("INSERT INTO `pd_cambiosdelivery` (`precio`) VALUES ($delivery)");
if($db->affected_rows()){
	echo '1';
}else echo $db->dblink->error;

die;

?>