<?php

session_start();
require '../db.php';
$datos=json_decode(file_get_contents('php://input'),true);

if(password_verify($datos['oldPassword'],$_SESSION['contraseña'])){
	$newHash=password_hash($datos['newPassword'],PASSWORD_DEFAULT);
	$res=$db->query("UPDATE `pd_vendedores` SET `contraseña`='$newHash' WHERE `ID`=".$_SESSION['ID']);
	if((int)($db->affected_rows())){
		$_SESSION['contraseña']=$newHash;
		session_regenerate_id();
		echo 1;
	}else echo -1;
}else echo 0;

die;

?>