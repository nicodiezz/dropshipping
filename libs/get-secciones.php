<?php
session_start(['read_and_close'=>true]);
define('DR',$_SERVER['DOCUMENT_ROOT']);
if(!isset($_SESSION['ID']))
	require DR.'/libs/header-location.php';
require '../libs/db.php';
$rawRes=$db->query("SELECT `ID`,`nombre`,`parentID` FROM `pd_secciones` WHERE `vendedorID`=".$_SESSION['ID']);
if($rawRes->num_rows){
	$rejunte=['Otros'];
	while($sec=$rawRes->fetch_assoc())
		$rejunte[$sec['ID']]=$sec['nombre'];
	die(json_encode($rejunte));
}else die('["Otros"]');
?>