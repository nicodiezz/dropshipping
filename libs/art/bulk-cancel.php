<?php
session_start(['read_and_close'=>true]);
define('DR',$_SERVER['DOCUMENT_ROOT']);

$data=(json_decode(file_get_contents('php://input'),true));

if(!(isset($data['articulosIDs']) && isset($_SESSION['ID'])))
	require DR.'/libs/header-location.php';

require '../db.php';
	
$IDsArr=array_map(fn($el)=>(int)$el,explode(',',$data['articulosIDs']));
$db->query('DELETE FROM `pd_articulos` WHERE `ID` IN('.join(',',$IDsArr).')');
$res=$db->affected_rows();
foreach($IDsArr as $ID)
	if(file_exists($path = '../../protected/'.md5($ID)))
		unlink($path);

if((int)$data['area']){
	$db->prepared('DELETE FROM `pd_secciones` WHERE `ID`=?','i',$data['area']);
	$res+=$db->affected_rows();
}

echo $res;
?>