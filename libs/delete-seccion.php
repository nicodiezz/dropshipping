<?php

require 'db.php';

session_start(['read_and_close'=>true]);
$data=json_decode(file_get_contents('php://input'),true);

if(isset($_SESSION['ID']) && $res=$db->query("SELECT `parentID` FROM `pd_secciones` WHERE `ID`={$data['secID']} AND `vendedorID`=".$_SESSION['ID'])){
	$parentID=$db->result($res);
	$db->query("UPDATE `pd_secciones` SET `parentID`=$parentID WHERE `parentID`={$data['secID']}");
	$db->query("UPDATE `pd_articulos` SET `seccionID`=$parentID WHERE `seccionID`={$data['secID']}");
	$db->query("DELETE FROM `pd_secciones` WHERE `ID`={$data['secID']}");
}

?>