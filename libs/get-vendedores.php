<?php

	define('DR',$_SERVER['DOCUMENT_ROOT']);
	session_start(['read_and_close'=>true]);
	if(!$_SESSION['isAdmin'])
		require DR.'/libs/header-location.php';
	require '../libs/db.php';
	$vendedores=$db->query('SELECT `ID`,`nombre`,`habilitado` FROM `pd_vendedores`');// join admins to vendedores
	echo json_encode(
		$vendedores->num_rows?
			$vendedores->fetch_all(MYSQLI_ASSOC)
			:[]
	);
	
?>