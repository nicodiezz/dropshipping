<?php

require 'db.php';
session_start(['read_and_close'=>true]);
$data=json_decode(file_get_contents('php://input'),true);
if(isset($_SESSION['ID']) && $db->query("SELECT 1 FROM `pd_secciones` WHERE `ID`={$data['secID']} AND `vendedorID`=".$_SESSION['ID'])->num_rows)
	$db->prepared("UPDATE `pd_secciones` SET `nombre`=? WHERE `ID`={$data['secID']}",'s',[$data['newName']]);

?>