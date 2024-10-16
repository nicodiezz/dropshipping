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
<div id="bulk-message">
		<div>
			<div id="bulk-form">
				<span>El archivo debe ser un ZIP extraído de la página o un archivo de Microsoft Excel que cumpla el siguiente formato:</span>
				<div id="bulk-format-holder">
					<table id="bulk-format">
					<tbody>
						<tr><td></td> <td>A</td> <td>B</td> <td>C</td> <td>D</td> <td>E</td> <td>...</td></tr>
						<tr><td>1</td> <td></td> <td><b>Códigos</b></td> <td><b>Nombres</b></td> <td><b>Descripciones</b></td> <td><b>Precios</b></td> <td></td></tr>
						<tr><td>2</td> <td></td> <td>Código 1</td> <td>Nombre 1</td> <td>Descripción 1</td> <td>Precio 1</td> <td></td></tr>
						<tr><td>3</td> <td></td> <td>Código 2</td> <td>Nombre 2</td> <td>Descripcion 2</td> <td>Precio 2</td> <td></td></tr>
						<tr><td>...</td> <td></td> <td>...</td> <td>...</td> <td>...</td> <td>...</td> <td></td></tr>
					</tbody>
					</table>
				</div>
				<span>Defina el área donde se agregarán los archivos.</span>
				<div id="bulk-area">
					<label>
						<input type="radio" name="bulk-area-radio" value=0 checked class="bulk-tiny-input"><select class="bulk-area-input" id="bulk-area-select" disabled>
							<option value>Cargando...</option>
						</select>
					</label>
					<label>
						<input type="radio" name="bulk-area-radio" value=1 class="bulk-tiny-input"><input class="bulk-area-input" type="text" placeholder="Nueva área..." disabled>
					</label>
				</div>
				<label style="display:none">
					<input class="bulk-tiny-input" id="bulk-destacado" type="checkbox" onchange="if(this.checked)showMessage('Recuerde que no se recomienda destacar muchos artículos, lo recomendado es entre 4 y 10.')">
					<span>Destacar Todos</span>
				</label>
				<button id="bulk-select">Seleccionar Archivo</button>
			</div>
		</div>
	</div>
	
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
			<div id=articulos>
				<div id="art-context">
					<div>
						<button><i class="fas fa-edit"></i>Modificar</button>
						<button><i class="fas fa-star"></i>Destacar</button>
						<button><i class="fas fa-eye-slash"></i>Ocultar</button>
						<button><i class="fas fa-trash"></i>Eliminar</button>
					</div>
				</div>
				<div id=art-inicio>
					<div id="busqueda">
						<input id="busqueda-input" type=text placeholder="Escriba aquí para filtrar artículos">
						<label>
							<input type="checkbox" id="busqueda-escondidos">
							<span>Ocultar artículos escondidos</span>
						</label>
					</div>
					<div id="art-bar">
						<button id="nuevo-art" class="new art">Nuevo Artículo</button>
						<button class="new sec">Nueva Sección</button>
						<button class="new" id="bulk">Subir desde archivo</button>
						<button id=export>Exportar Artículos</button>
						<div id="art-export-panel">
							<button id="art-export-panel-cancel">Cancelar</button>
							<button id="art-export-panel-export">Exportar</button>
							<button id="art-export-panel-reset">Reiniciar selección</button>
							<button id="art-export-panel-toggle" title="Solo cambia la selección de los artículos a la vista.">Alternar selección</button>
						</div>
					</div>
					<div id="art-grid" class="hidden-scroll"></div>
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
			</div>
		</div>

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