<?php

session_start();

if(!isset($_SESSION['ID'])) /*&& isset($data['horarioID'])*/
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

$data=json_decode(file_get_contents('php://input'),true);

require '../db.php';

switch((int)$data['propID']){
	case 0:
		$column='lunes';
		$isNumber=true;
		break;
	case 1:
		$column='martes';
		$isNumber=true;
		break;
	case 2:
		$column='miercoles';
		$isNumber=true;
		break;
	case 3:
		$column='jueves';
		$isNumber=true;
		break;
	case 4:
		$column='viernes';
		$isNumber=true;
		break;
	case 5:
		$column='sabado';
		$isNumber=true;
		break;
	case 6:
		$column='domingo';
		$isNumber=true;
		break;
	case 7:
		$column='desde';
		$isNumber=false;
		break;
	case 8:
		$column='hasta';
		$isNumber=false;
		break;
}

$query=[
	"UPDATE `pd_horarios` SET `$column`="
	,' WHERE `ID`='.$data['horarioID']
];

if($isNumber)
	$db->query($query[0].(int)$data['value'].$query[1]);
else $db->prepared($query[0].'?'.$query[1],'s',$data['value']);

?>