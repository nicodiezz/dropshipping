<?php

$horarioID=json_decode(file_get_contents('php://input'),true)['horarioID'];
session_start(['read_and_close'=>true]);
require '../db.php';

if($db->query("SELECT * FROM `pd_horarios` WHERE `vendedorID`={$_SESSION['ID']} AND `ID`=".$horarioID)->num_rows){
	$db->query("DELETE FROM `pd_horarios` WHERE `ID`=".$horarioID);
	echo $db->affected_rows();
}else echo '0';
?>