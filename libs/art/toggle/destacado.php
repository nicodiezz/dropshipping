<?php

session_start(['read_and_close'=>true]);
define('DR',$_SERVER['DOCUMENT_ROOT']);
if(!$_SESSION['isAdmin'])
	require DR.'/libs/header-location.php';
	
$data=json_decode(file_get_contents('php://input'),true);

$ID=(int)$data['isAdmin'];
require '../../db.php';
$db->query("UPDATE `pd_articulos` SET `destacado`=".((int)$data['value'])." WHERE `ID`=$ID AND `grupoID`=".$_SESSION['isAdmin']);
echo (int)$db->affected_rows();
die;

?>