<?php
session_start(['read_and_close'=>true]);
if(!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin'])
	require $_SERVER['DOCUMENT_ROOT'].'/libs/header-location.php';
require 'libs/db.php';

$personalizacion=parse_ini_file('personalizacion.ini');
$nombre=$personalizacion['nombre'];

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="author" content="c3r38r170"/>
	<title><?=$nombre?> - Panel de Administrador</title>

	<link rel="stylesheet" href="//use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="/css/variables.css">
	<link rel="stylesheet" href="/css/admin.css">
	
	<script src="/libs/c3tools.js"></script>
	<script src="/admin/main.js"></script>
	
</head>
<body>
	<div id="header">
		<span id="header-anvorgesa" onclick="gEt('anvorgesa').classList.add('anvorgesa-abierta')">☰</span>
		<h1>Bienvenido, <span id=nombre><?=$_SESSION['nombre']?></span></h1>
	</div>
	
	<div id=anvorgesa>
		<div id="anvorgesa-header">
			<img src="/img/logo.png" alt="Logo" id=logo>
			<div id="anvorgesa-header-nombre"><?=$_SESSION['nombre']?></div>
		</div>
		<div id="anvorgesa-ingredientes">
			<button class="action" value=4>
				<i class="fa fa-user"></i>
				<span>Cuenta</span>
			</button>
			<button class="action selected" value=1>
				<i class="fa fa-people-line"></i>
				<span>Vendedores</span>
			</button>
			<button class="action" value=2>
				<i class="fa fa-list"></i>
				<span>Grupos</span>
			</button>
			<button class="action" value=3>
				<i class="fa fa-file-invoice"></i>
				<span>Reclamos</span>
			</button>
			<button class="action" id=salir>
				<i class=""></i>
				<span>Cerrar Sesión</span>
			</button>
		</div>
	</div>

	<div id="realPanel">

		<div id="ven">
			<div id="ven-list">
				<div>
					<input placeholder="Escriba para filtrar" type="text" id="ven-list-filter">
				</div>
				<div id="ven-list-buttons"></div>
			</div>
			<label>Habilitación: <input id=habilitado type="checkbox" disabled></label>
			<h1>Resumen<small> de <span id="ven-nombre">...</span></small></h1>
			<div class=dates>
				<label for=from><span>Desde</span></label>
				<input id=from type="date" disabled>
				<label for=until><span>Hasta</span></label>
				<input id=until type="date" disabled>
				<button id=ven-buscar disabled>Buscar</button>
				<!-- TODO UX: bloquear si no hay ventas? quizas bloquear el boton de actuales... -->
				<button id=ven-exportar disabled>Exportar</button>
			</div>
			<div id=ven-pedidos></div>
		</div>
		
		<div id="grupos" class=notSelectedPanel>
			<button id="grupo-add">Añadir</button>
			<div id="grupos-container">
			</div>
		</div>
		<div id="grupo" class=notSelectedPanel>
			<h1 id="titulo-grupo"></h1>
			<input type="text" placeholder="Escribe para filtar artículos">
			<div class="articulos-botones">	
				<button onclick="abrirEditor(0,0)" class="articulos-boton" id="add-art">Nuevo artículo</button>
				<button class="articulos-boton" id="add-sec">Nueva sección</button>
				<button class="articulos-boton" id="bulk">Subir desde archivo</button>
				<button class="articulos-boton" id="export">Exportar artículos</button>
			</div>
		</div>
		<form id="art-añadir" class="hidden-scroll notSelectedPanel">
			<fieldset>
				<button type=button class="cute-button" id="art-añadir-volver">Volver</button>
				<h3 class="art-añadir-title">Datos del producto</h3>
				<p>Nombre</p>
				<input name=Nombre title="Nombre del Artículo" required class=cool-input>
				<p>Descripción</p>
				<textarea class=cool-input name=Descripcion title="Descripción detallada"></textarea>
				<p>Sección</p>
				<select class=cool-input name=Seccion id=new-art-sec-input disabled>
					<option value>Cargando...</option>
				</select>
				<p>Precio</p>
				<div id="art-precio-holder">
					<span>$ </span><input class="cool-input" name=Precio type="number" step="0.01" required>
				</div>
				<p>Código de barra</p>
				<input class=cool-input name="codigo" title="Código de Barra (13 números)" maxlength="13">
				<h3>Foto del producto</h3>
				<div class=imagePicker>
					<img src="img/not-found.png">
					<input type=file name=foto class="cute-button">
					<button type=button onclick="resetImagePicker(this,'img/articulo.php?ID='+editing)" class="cute-button hidden">Reestablecer</button>
				</div>
				<input id="art-subir-boton" type=submit value="Subir Artículo" class="cute-button end-button">
			</fieldset>
		</form>

		<div id="rec" class=notSelectedPanel>
		</div>

		<div id="cue" class=notSelectedPanel>
			<p>Nombre:</p>
			<div id="cue-name">
				<input placeholder="<?=$_SESSION['nombre']?>" value="<?=$_SESSION['nombre']?>" id="cue-name-input">
				<button id="cue-name-update">Actualizar</button>
			</div>
			
			<button id="cue-pass">Cambiar contraseña</button>
		</div>

	</div>
	<div id="grupo-hasVends">
		<div id="grupo-hasVends-body">
			<span>Se han encontrado <span id="grupo-hasVends-body-cuantos"></span> en ese grupo.</span>
			<span>¿Cómo desea continuar?</span>
			<div id="grupo-hasVends-body-buttons">
				<button value=1>Eliminar de todos modos</button>
				<button value=0>Cancelar</button>
			</div>
		</div>
	</div>
</body>
</html>