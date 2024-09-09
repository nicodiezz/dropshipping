<?php
$input=trim(file_get_contents('php://input'));
if(!$input)
	die;
$data=json_decode($input,true);
$location=explode(',',$data['location']);
require 'db.php';
if(!($categoriaID=(int)$data['categoria'])){
	$db->prepared("INSERT INTO `pd_categorias` (`nombre`) VALUES (?)",'s',$data['categoria']);
	$categoriaID=$db->insert_id();
}
require 'utf8-to-ascii.php';
$db->prepared(
	"INSERT INTO `pd_vendedores` (`usuario`,`contraseña`,`numero`,`correo`,`lat`,`lon`,`nombre`,`ciudad`,`direccion`,`provincia`,`pais`,`nombreURL`,`categoriaID`,`color`) VALUES (?,'".password_hash($data['password'],PASSWORD_DEFAULT)."',".((int)$data['numero']).",'',?,?,?,?,?,?,?,?,$categoriaID,UPPER(SUBSTRING(MD5(RAND()),1,6)))"
	,'sssssssss'
	,[$data['username'],$location[1],$location[0],$data['negocioname'],$data['ciudad'],$data['direccion'],$data['provincia'],$data['pais'],toAsciiURL($data['negocioname'])]
);

session_start();
$_SESSION=$db->query("SELECT * FROM `pd_vendedores` WHERE `ID`=".$db->insert_id())->fetch_assoc();

die('1');
?>