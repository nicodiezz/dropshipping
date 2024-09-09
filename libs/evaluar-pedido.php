<?php
session_start(['read_and_close'=>true]);
require 'db.php';

$data=json_decode(file_get_contents('php://input'),true);
$data['confirmado']=(int)$data['confirmado'];
$data['pedidoID']=(int)$data['pedidoID'];

if(!(isset($_SESSION['ID']) && $db->query("SELECT 1 FROM `pd_pedidos_metadatos` WHERE `vendedorID`=".$_SESSION['ID']." AND `ID`=".$data['pedidoID'])->num_rows))
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';
$db->query("UPDATE `pd_pedidos_metadatos` SET `concretado`=".$data['confirmado']." WHERE `ID`=".$data['pedidoID']);

echo $db->affected_rows();
?>