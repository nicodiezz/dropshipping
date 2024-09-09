<?php

session_start(['read_and_close'=>true]);
$data=json_decode(file_get_contents('php://input'),true);
if(!(isset($_SESSION['isAdmin']) && isset($data['newName']) && trim($data['newName']) && isset($data['ID']) && ($data['ID']=(int)$data['ID'])))
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

require '../db.php';
echo $db->prepared("UPDATE `pd_categorias` SET `nombre`=? WHERE `ID`=".$data['ID'],'s',trim($data['newName']));
	
?>