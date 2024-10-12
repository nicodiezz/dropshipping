<?php

session_start(['read_and_close'=>true]);
$data=json_decode(file_get_contents('php://input'),true);
if(!(isset($_SESSION['isAdmin']) && isset($data['nombre']) && isset($data['comision'])))
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';

require '../db.php';

if ($db->prepared('INSERT INTO `pd_grupos` (`nombre`, `comision`) VALUES (?, ?)', 'sd', trim($data['nombre']), (float)$data['comision']))
	echo $db->insert_id();
else echo 0;
?>