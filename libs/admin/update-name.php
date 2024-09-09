<?php

session_start();
if(!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin'])
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

$nombre=trim(json_decode(file_get_contents('php://input'),true)['name']);
require '../db.php';

if($db->prepared('UPDATE `pd_admins` SET `nombre`=? WHERE `ID`='.$_SESSION['ID'],'s',$nombre)){
	$_SESSION['nombre']=$nombre;
	echo '1';
}else echo $db->dblink->error;

die;

?>