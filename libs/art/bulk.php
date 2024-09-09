<?php
session_start(['read_and_close'=>true]);
define('DR',$_SERVER['DOCUMENT_ROOT']);

if(!(isset($_FILES['articulos']) && isset($_SESSION['ID'])))
	require DR.'/libs/header-location.php';

function badFormat($arg){
	die("[-1,$arg]");
}

$extensionSubida=pathinfo($_FILES['articulos']['name'],PATHINFO_EXTENSION);
$subioUnZip = $extensionSubida=='zip';
if($subioUnZip){
	$za=new ZipArchive();
	$za->open($_FILES['articulos']['tmp_name']);
	if($dataDelExcel=$za->getFromName('articulos.xlsx')
	)
		$extension='XLSX';
	else if($dataDelExcel=$za->getFromName('articulos.xls')
	)
		$extension='XLS';
	else badFormat(1);
	$className='Simple'.$extension;
}else{
	$className='Simple'.strtoupper($extensionSubida);
	$dataDelExcel=$_FILES['articulos']['tmp_name'];
}

require DR.'/libs/PHP/utils/Excel/'.$className.'.php';
if($excel=new $className($dataDelExcel,$subioUnZip) )
	$rows=$excel->rows();
else badFormat(2);

$response=[1,[
	'arts'=>[]
	// ,'log'=>''
]];

require '../db.php';

if((int)$_POST['area']['isNew']){
	$db->prepared('INSERT INTO `pd_secciones` (`nombre`,`vendedorID`) VALUES (?,'.$_SESSION['ID'].')','s',$_POST['area']['data']);
	$secID=$db->insert_id();
	$response[1]['newAreaID']=$secID;
}else $secID=(int)$_POST['area']['data'];

$l=count($rows);
if($l<=1)
	die('[0]');
unset($rows[0]);

define('NOMBRE_DE_IMAGEN',0);
define('CODIGO',1);
define('NOMBRE',2);
define('DESCRIPCION',3);
define('PRECIO',4);

$imagenesIDsPairs=[];

$valuesForVendIDYSecID=",{$_SESSION['ID']},$secID,";
$siDestacarONo=','.(int)$_POST['destacar'];

foreach ($rows as $row){
	// $response[1]['log'].=print_r($row,true);
	
	$nombre=trim($row[NOMBRE]);
	if(!($nombre && is_numeric($row[CODIGO]) && is_numeric($row[PRECIO])))
		continue;
	
	$precio=(float)$row[PRECIO];
	if(
		$subioUnZip
		&& strpos($imageName=trim($row[NOMBRE_DE_IMAGEN]),'.')!==false
		&& $fileContents=$za->getFromName($imageName)
	){
		$db->prepared(
			'INSERT INTO `pd_articulos`
				(`nombre`,`descripcion`,`precio`,`vendedorID`,`seccionID`,`codigo_de_barras`,`destacado`,`ext_de_img`)
				VALUES
				(?,?,'.$precio.$valuesForVendIDYSecID.(int)$row[CODIGO].$siDestacarONo.',?)'
			,'sss'
			,[$nombre,trim($row[DESCRIPCION]),pathinfo($imageName,PATHINFO_EXTENSION)]
		);
		$id=$db->insert_id();
		file_put_contents('../../protected/'.md5($id),$fileContents);
	}else{
		$db->prepared('INSERT INTO `pd_articulos` (`nombre`,`descripcion`,`precio`,`vendedorID`,`seccionID`,`codigo_de_barras`,`destacado`) VALUES (?,?,'.$precio.$valuesForVendIDYSecID.(int)$row[CODIGO].$siDestacarONo.')','ss',[$nombre,trim($row[DESCRIPCION])]);
		$id=$db->insert_id();
	}
	
	$db->query("INSERT INTO `pd_cambiosdeprecio` (`articuloID`,`nuevoPrecio`) VALUES ($id,$precio)");
	
	$response[1]['arts'][]=$db->query("SELECT * FROM `pd_articulos` WHERE `ID`=".$id)->fetch_assoc();
}

die(json_encode($response));
?>