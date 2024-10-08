<?php
$input=trim(file_get_contents('php://input'));
if(!$input)
	die;
$data=json_decode($input,true);
require 'db.php';
if(!($categoriaID=(int)$data['categoria'])){
	$db->prepared("INSERT INTO `pd_categorias` (`nombre`) VALUES (?)",'s',$data['categoria']);
	$categoriaID=$db->insert_id();
}
require 'utf8-to-ascii.php';
$db->prepared( 
    "INSERT INTO `pd_vendedores` (`usuario`,`contraseña`,`numero`,`correo`,`lat`,`lon`,`nombre`,`ciudad`,`direccion`,`provincia`,`pais`,`nombreURL`,`categoriaID`,`color`) VALUES (?,'".password_hash($data['password'],PASSWORD_DEFAULT)."',".((int)$data['numero']).",'',0,0,?,'','','','',?,$categoriaID,UPPER(SUBSTRING(MD5(RAND()),1,6)))"
    ,'sss'
    ,[$data['username'],$data['negocioname'],toAsciiURL($data['negocioname'])] 
);
	

session_start();
$_SESSION=$db->query("SELECT * FROM `pd_vendedores` WHERE `ID`=".$db->insert_id())->fetch_assoc();

die('1');
?>