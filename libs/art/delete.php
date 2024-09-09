<?php

session_start(['read_and_close'=>true]);
define('DR',$_SERVER['DOCUMENT_ROOT']);
if(!isset($_SESSION['ID']) || $_SESSION['isAdmin'])
	require DR.'/libs/header-location.php';
	
$ID=(int)(json_decode(file_get_contents('php://input'),true)['ID']);
require '../db.php';
$db->query("UPDATE `pd_articulos` SET `disponible`=0 WHERE `ID`=$ID AND `vendedorID`=".$_SESSION['ID']);
if($db->affected_rows()){
	if(file_exists($img='../../protected/'.md5($ID)))
		unlink($img);
	echo 1;
}else echo 0;
die;

?>