<?php

session_start(['read_and_close'=>true]);
$data=json_decode(file_get_contents('php://input'),true);
if(!(isset($_SESSION['isAdmin']) && isset($data['ID']) && isset($data['newID'])))
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

require '../db.php';
$data['newID']=(int)$data['newID'];
$data['ID']=(int)$data['ID'];
if($data['newID']){
	$db->query("UPDATE `pd_vendedores` SET `categoriaID`=".$data['newID'].' WHERE `categoriaID`='.$data['ID']);
	$affRows=(int)($db->affected_rows());
}else $affRows=1;
if($affRows){
	$db->query("DELETE FROM `pd_categorias` WHERE `ID`=".$data['ID']);
	echo $db->affected_rows();
}else echo 0;
	
?>