<?php

session_start(['read_and_close'=>true]);
define('DR',$_SERVER['DOCUMENT_ROOT']);
if(!isset($_SESSION['ID']) || $_SESSION['isAdmin'])
	require DR.'/libs/header-location.php';
	
$data=json_decode(file_get_contents('php://input'),true);

$ID=(int)$data['ID'];
require '../../db.php';
$db->query("UPDATE `pd_articulos` SET `destacado`=".((int)$data['value'])." WHERE `ID`=$ID AND `vendedorID`=".$_SESSION['ID']);
echo (int)$db->affected_rows();
die;

?>