<?php
$credentials=json_decode(file_get_contents('php://input'),true);
if(!(isset($credentials['user']) && isset($credentials['pass'])))
	die;
$user=trim($credentials['user']);
$pass=trim($credentials['pass']);
if(!($user && $pass))
	die;
require 'db.php';
$res=$db->prepared('SELECT * FROM `pd_vendedores` WHERE `usuario`=?','s',$user);
if($res->num_rows){
	$res=$res->fetch_assoc();
	if((int)$res['habilitado'])
		if(password_verify($pass,$res['contraseña'])){
			$res['isAdmin']=false;
			session_start();
			$_SESSION=$res;
			session_regenerate_id();
			die('4');
		}else die('2');
	else die('3');
}else{
	$res=$db->prepared('SELECT * FROM `pd_admins` WHERE `usuario`=?','s',$user);
	if($res->num_rows){
		$res=$res->fetch_assoc();
		if((int)$res['habilitado'])
			if(password_verify($pass,$res['contraseña'])){
				$res['isAdmin']=true;
				session_start();
				$_SESSION=$res;
				session_regenerate_id();
				die('5');
			}else die('2');
		else die('3');
	}else die('1');
}
?>