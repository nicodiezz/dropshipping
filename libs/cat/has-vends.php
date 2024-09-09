<?php

session_start(['read_and_close'=>true]);
$data=json_decode(file_get_contents('php://input'),true);
if(!(isset($_SESSION['isAdmin']) && isset($data['ID'])))
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';
	
require '../db.php';
echo $db->result('SELECT COUNT(1) FROM `pd_vendedores` WHERE `categoriaID`='.(int)$data['ID']);

?>