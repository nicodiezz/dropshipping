<?php

$response=[
	'ok'=>false
];

session_start(['read_and_close'=>true]);

if($_SESSION['ID']){
	$response['ok']=true;
	$response['data']=[];
	
	require '../db.php';
	
	if(($hors=$db->query("SELECT *,TIME_FORMAT(`desde`,'%H:%i') AS `desde`,TIME_FORMAT(`hasta`,'%H:%i') AS `hasta` FROM `pd_horarios` WHERE `vendedorID`=".$_SESSION['ID']))->num_rows)
		while($hor=$hors->fetch_assoc())
			$response['data'][]=[
				'ID'=>$hor['ID']
				,'desde'=>$hor['desde']
				,'hasta'=>$hor['hasta']
				,'dias'=>[
					$hor['lunes']
					,$hor['martes']
					,$hor['miercoles']
					,$hor['jueves']
					,$hor['viernes']
					,$hor['sabado']
					,$hor['domingo']
				]
			];
}

die(json_encode($response));

?>