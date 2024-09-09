<?php

session_start(['read_and_close'=>true]);
if(!isset($_SESSION['ID']))
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';
$data=json_decode(file_get_contents('php://input'),true);

if(trim($data['nombre'])){
	require 'db.php';
	$db->prepared("INSERT INTO `pd_secciones` (`nombre`,`vendedorID`,`parentID`) VALUES (?,{$_SESSION['ID']},{$data['parentID']})",'s',$data['nombre']);
	echo json_encode($db->query('SELECT * FROM `pd_secciones` WHERE `ID`='.$db->insert_id())->fetch_assoc());
}

?>