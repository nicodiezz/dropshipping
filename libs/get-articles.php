<?php
session_start(['read_and_close'=>true]);
define('DR',$_SERVER['DOCUMENT_ROOT']);
if(!isset($_SESSION['ID']))
	require DR.'/libs/header-location.php';
require '../libs/db.php';
$rawRes=$db->query("SELECT * FROM `pd_articulos` WHERE `disponible`<>0 AND `vendedorID`=".$_SESSION['ID']);
if($rawRes->num_rows){
	$rejunte=[];
	while($art=$rawRes->fetch_assoc())
		$rejunte[]=$art;
	die(json_encode($rejunte));
}else die('[]');
?>