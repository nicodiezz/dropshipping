<?php

$data=json_decode(file_get_contents('php://input'),true);

if(
	isset($data['objeto'])
	&& isset($data['complaint'])
	&& is_numeric($data['objeto'])
	&& $data['complaint']=trim($data['complaint'])
){
	require 'db.php';
	echo $db->prepared("INSERT INTO `pd_reclamos` (`fecha`,`objeto`, `reclamo`) VALUES (CURDATE(),{$data['objeto']},?)",'s',$data['complaint'])?
		1
		:0;
}else echo 0;

?>