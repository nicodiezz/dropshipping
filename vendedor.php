<?php

define('DR',$_SERVER['DOCUMENT_ROOT']);
define('HEADER_LOCATION',DR.'/libs/header-location.php');
require 'libs/db.php';

if(isset($_GET['ID']))
	$thisVendedor=$db->query('SELECT * FROM `pd_vendedores` WHERE `habilitado`=1 AND `ID`='.(int)$_GET['ID']);
elseif(isset($_GET['prettyURL']))
	$thisVendedor=$db->prepared('SELECT * FROM `pd_vendedores` WHERE `nombreURL`=? AND `habilitado`=1','s',$_GET['prettyURL']);
else require HEADER_LOCATION;

if(!$thisVendedor->num_rows)
	require HEADER_LOCATION;
$thisVendedor=$thisVendedor->fetch_assoc();

$personalizacion=parse_ini_file('personalizacion.ini');
$nombre=$personalizacion['nombre'];

$horarios=$db->query("SELECT *,TIME_FORMAT(`desde`,'%H:%i') AS `desde`,TIME_FORMAT(`hasta`,'%H:%i') AS `hasta` FROM `pd_horarios` WHERE `vendedorID`=".$thisVendedor['ID']);

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="author" content="c3r38r170"/>
	
	<title><?=$nombre?> - <?=$thisVendedor['nombre']?></title>
	<meta property="og:title" content="<?=$nombre?> - <?=$thisVendedor['nombre']?>"/>
	<meta property="og:description" name="description" content="<?=$thisVendedor['descripcion']?>">
	<meta property="og:image" content="img/logo.php?vendedorID=<?=$thisVendedor['ID']?>"/>
	<meta property="og:url" content="https://<?=$_SERVER['SERVER_NAME']?>/vendedor.php?ID=<?=$thisVendedor['ID']?>"/>
	
	<link rel="stylesheet" href="//use.fontawesome.com/releases/v6.1.1/css/all.css">
	<link rel="stylesheet" href="/css/variables.css">
	<link rel="stylesheet" href="/css/vendedores.css">
	<!-- TODO usar SCSS -->
	<style>
		html{
			background:#<?=$thisVendedor['color']?>;
		}
	</style>
	<script src="/libs/c3tools.js"></script>
	<script>
		const NÚMERO=<?=$thisVendedor['numero']?>,VENDEDOR_ID=<?=$thisVendedor['ID']?>;
		var articulosSeleccionados={}
			,sum=0
			,abierto=false
			,searchID=0
			,minSum=<?=$thisVendedor['minimoCompra']?>
			
			,suma
			,confirmarYenviar
			;
		
		defaultMessageBorderColor='#<?=$thisVendedor['color']?>';
		
		function normalizarNumero(numero){
			return Math.round(numero * 1000)/1000;
		}
		
		//buscador
		
		function startSearch(queryString){
			let myID=++searchID;
			setTimeout(()=>{
				if(searchID==myID)
					for(let articulo of [...SqS('.articulo',ALL)]){
						let isHidden=articulo.classList.contains('hidden');
						if(test(queryString,articulo.dataset.serializable)){
							if(isHidden){
								articulo.classList.remove('hidden');
								
								let seccion=articulo;
								while((seccion=seccion.previousElementSibling).tagName!='H2' && seccion.classList.contains('hidden'))
								;
								if(!seccion.classList.contains('hidden'))
									continue;
										
								do
									seccion.classList.remove('hidden');
								while(seccion.dataset.parent!=0 && (seccion=SqS(`#content > h2[data-id="${seccion.dataset.parent}"]`)).classList.contains('hidden'));
							}
						}else if(!isHidden){
							articulo.classList.add('hidden');
						
							let seccion=articulo
								,seccionParent;
							while((seccion=seccion.previousElementSibling).tagName!='H2');
							seccionParent=seccion.dataset.parent;
							seccionID=seccion.dataset.id;
							
							let nextSibling=articulo;
							while(
								(nextSibling=nextSibling.nextElementSibling).id!='no-article'
								&&(
									nextSibling.classList.contains('hidden')
									&&(
										nextSibling.tagName=='DIV'
										||nextSibling.dataset.parent!=seccionID
									)
								)
							);
							if(
								nextSibling.id!='no-article'
								&& (
									nextSibling.tagName=='DIV'
									|| nextSibling.dataset.parent==seccionID
								)
							)
								continue;
								
							nextSibling=articulo;
							
							while(nextSibling=nextSibling.previousElementSibling){
								if(nextSibling.tagName=='DIV'){
									//solo puede ser  sobrino o descendiente de hermano
										//originalmente este comentario iba en otro lado...
									if(!nextSibling.classList.contains('hidden'))
										break;
								}else if(nextSibling.tagName=='H2'){
									if(!nextSibling.classList.contains('hidden'))
										if(nextSibling.dataset.id==seccionParent){
											nextSibling.classList.add('hidden');
											seccionParent=nextSibling.dataset.parent;
											seccionID=nextSibling.dataset.id;
										}else if(nextSibling.dataset.parent==seccionParent)
											nextSibling.classList.add('hidden');
								}
							}
						}
					}
			},350);
		}
		
		function resetSearch(){
			for(let articulo of [...SqS('.hidden',ALL)])
				articulo.classList.remove('hidden');
			searchID++;
			return true;
		}
		
		function test(queryString,artText){
			return new RegExp('.*?'+queryString+'.*?','i').test(artText);
		}
		
		
		
		function build(img){
			let data=JSON.parse(img.parentNode.dataset.serializable)
				,articulo=img.parentNode;
			articulo.appendChild(createNode(
				D.createDocumentFragment()
				,{
					children:[
						['H3',{innerText:data[0],class:'nombre'}]
						,['SPAN',{
							class:'precio'
							,innerText:articulo.dataset.precio
						}]
						,[
							'P',{
								innerText:data[1]
								,class:'descripcionDelArticulo'
							}
						]
						,[
							'DIV',{
								class:'controles'
								,children:[
									['BUTTON',{
										value:0
										,disabled:true
										,innerText:'-'
									}]
									,['SPAN',{
										class:'contador'
										,innerText:0
									}]
									,['BUTTON',{
										value:1
										,innerText:'+'
									}]
								]
							}
						]
					]
				}
			));
			delete articulo.dataset.precio;
			articulo.dataset.serializable=articulo.dataset.serializable.normalize('NFD').replace(/[\u0300-\u036f]/g,"");
		}
		
		function actualizarCantidadesYPrecios(item,cantidad,precio){
			item.children[0].innerText=cantidad;
			item.children[2].innerText=normalizarNumero(precio*cantidad);
		}
		
		addEventListener('DOMContentLoaded',()=>{
			
			//DOM bonding
			
			suma=gEt('resumen-grandote-total');
			
			//events handlers
			
			gEt('resumen-header-cerrar').onclick=function(){
				if(gEt('resumen').classList.contains('resumen-abierto'))
					gEt('resumen').classList.replace('resumen-abierto','resumen-cerrado');
			}
			
			gEt('resumen-chiquito').onclick=function(e){
				if(gEt('resumen').classList.contains('resumen-cerrado'))
					gEt('resumen').classList.replace('resumen-cerrado','resumen-abierto');
				else{
					let items=[...SqS('.resumen-item',ALL)];
					if(items.length){
						if(sum<minSum){
							showMessage('La compra mínima es de $\u00a0'+minSum);
							return;
						}
						
						let campos=[...SqS('input,select',ALL,gEt('resumen'))].map(element=>element.value.trim());
						if(campos[0] && campos[1] && campos[2]){
							let mensaje=`Hola! Soy ${campos[0]}.\nQuisiera hacer el siguiente pedido:\n\n`
								,pedido={
									vendedorID:VENDEDOR_ID
									,nombre:campos[0]
									,direccion:campos[1]
									,delivery:!!+campos[3]
									,items:[]
								};
								
							let delivery;
							for(let item of items){
								if(!+item.dataset.id){
									delivery=item;
									continue;
								}

								let datos=[].map.call(item.children,e=>e.innerText);
								mensaje+=' - '+datos[0]+' × '+datos[1]+' ($ '+datos[2]+')\n';
								
								let controlesDelArticulo=SqS('.articulo[data-id="'+item.dataset.id+'"]').lastElementChild.children;
								controlesDelArticulo[0].disabled=true;
								controlesDelArticulo[1].innerText=0;
								
								item.remove();
							}
							if(delivery)
								mensaje+=` - Delivery ($ ${delivery.children[2].innerText})`;
							mensaje+='\n*Total: $'+suma.innerText+'*\n\nDirección de entrega: '+campos[1]+'\nForma de pago: '+campos[2]+'\n¡Muchas Gracias!';
							open('https://wa.me/'+NÚMERO+'?text='+encodeURIComponent(mensaje));
							
							for(let itemID in articulosSeleccionados){
								let count=articulosSeleccionados[itemID];
								if(count){
									pedido.items.push([itemID,count]);
									
									articulosSeleccionados[itemID]=0;
								}
							}
							sendJSON('libs/asentar-pedido.php',pedido);
							
							suma.innerText=sum=0;
						}else showMessage('¡Completá tus datos antes de hacer el pedido!');
					}else showMessage('¡Debe elegir algo antes de hacer el pedido!');
				}
			}
			
			if(navigator.userAgent.includes('Mobi'))
				gEt('busqueda').onchange=function(){
					let realValue=this.value.trim();
					
					if(!realValue){
						this.dataset.previous='';
						resetSearch();
					}else{
						startSearch(
							realValue
							// ,realValue.indexOf(this.dataset.previous)!=-1
						);
						this.dataset.previous=realValue;
					}
				};
			else
				gEt('busqueda').onkeyup=function(e){
					let realValue=this.value.trim();
					if(!realValue && e.which==8)
						resetSearch();
					else if(realValue)
						startSearch(
							realValue
							// ,e.which==8
						);
				};
			
			gEt('content').onclick=e=>{
				let target=e.target;
				switch(target.nodeName){
				case 'BUTTON':
					let contador=SqS('.contador',ONLY_ONE,target.parentNode)
						,precio=+target.parentNode.previousElementSibling.previousElementSibling.innerText
						,currentSum=+contador.innerText
						,thisArticulo=target.closest('.articulo')
						,artID=thisArticulo.dataset.id
						,buttonValue=+target.value
						,nuevaSumaTotal
						,cantidadChiquito=gEt('resumen-chiquito-cantidad')
						;
						
					if(buttonValue){//+
						contador.innerText=++currentSum;
						if(currentSum==1)
							contador.previousElementSibling.disabled=false;
							
						let resumenItem=SqS('.resumen-item[data-id="'+artID+'"]');
						if(!resumenItem)
							resumenItem=gEt('resumen-grandote-items').appendChild(createNode('DIV',{
								dataset:{id:artID}
								,class:'resumen-item'
								,children:[
									[
										'DIV'
										,{innerText:1}
									]
									,[
										'DIV'
										,{innerText:thisArticulo.children[1].innerText}
									],
									,[
										'DIV'
										,{
											innerText:precio
											,class:'precio'
										}
									]
								]
							}));
						else actualizarCantidadesYPrecios(resumenItem,currentSum,precio);
						
						nuevaSumaTotal=(+cantidadChiquito.innerText)+1;
					}else{//-
						if(currentSum<=0){
							target.disabled=true;
							return;
						}
						
						contador.innerText=--currentSum;
						let resumenItem=SqS('.resumen-item[data-id="'+artID+'"]');
						if(currentSum)
							actualizarCantidadesYPrecios(resumenItem,currentSum,precio);
						else{
							if(resumenItem)
								resumenItem.remove();
							target.disabled=true;
						}
						
						nuevaSumaTotal=(+cantidadChiquito.innerText)-1||'';
					}
					
					
					cantidadChiquito.innerHTML=nuevaSumaTotal;
						
					articulosSeleccionados[artID]=currentSum;
					
					//TODO mejorar
					suma.innerText = sum = normalizarNumero(buttonValue?
						sum+=precio
						:sum-=precio
					);
					gEt('resumen-chiquito-precio').innerText=sum?'$\u00a0'+sum:'';
					break;
				case 'H2':
					target.classList.toggle('categoria-cerrada');
					let escondiendo=target.classList.contains('categoria-cerrada')
						,accion=escondiendo?'add':'remove'
						,descendientes=[target.dataset.id]
						,previouslyHidden=[];
					let algoDentro=target
						,inHidden=false;
					while(algoDentro=algoDentro.nextElementSibling){
						if(algoDentro.tagName=='H2'){
							if(!descendientes.includes(algoDentro.dataset.parent)){
								break;
							}
							descendientes.push(algoDentro.dataset.id);
							
							// TODO probar sin lo que viene tras el ||
							inHidden=algoDentro.classList.contains('categoria-cerrada') || previouslyHidden.includes(algoDentro.dataset.parent);
							// TODO DRY
							if(inHidden)
								previouslyHidden.push(algoDentro.dataset.id);
							
							if(!previouslyHidden.includes(algoDentro.dataset.parent))
								algoDentro.classList[accion]('hidden-by-user');
						}
						if(!inHidden)
							algoDentro.classList[accion]('hidden-by-user');
					}
					break;
				}
			};
			if(gEt('destacados'))
				gEt('destacados').onclick=gEt('content').onclick;
			
			gEt('resumen-grandote-datos').onchange=e=>{
				let target=e.target;
				if(target.nodeName=='INPUT')
					localStorage.setItem(target.id.split('-')[1],target.value);
			}
			
			gEt('search').onclick=function(){
				B.classList.toggle('searching');
			}
			
			//building UI
			
			for(let info of ['nombre','domicilio','formaDePago']){
				let gottenInfo=localStorage.getItem(info);
				if(gottenInfo && (gottenInfo=gottenInfo.trim()))
					gEt('changuito-'+info).value=gottenInfo;
			}

			gEt('changuito-retiro').onchange=function(){
				if(this.value==1){
					gEt('resumen-grandote-items').appendChild(createNode('DIV',{
						class:'resumen-item'
						,id:'resumen-item-delivery'
						,children:[
							'DIV'
							,[
								'DIV'
								,{innerText:'Delivery'}
							],
							,[
								'DIV'
								,{
									innerText:+this.dataset.value
									,class:'precio'
								}
							]
						]
					}));
				}else{
					let deliveryElement=gEt('resumen-item-delivery')
					if(deliveryElement)
						deliveryElement.remove();
				}

			}
			
		});
		
	</script>
</head>
<body>
	<div id=header class="noventaPorciento">
		<img src="img/logo.php?vendedorID=<?=$thisVendedor['ID']?>">
		<h1><?=$thisVendedor['nombre']?></h1>
		<input id="busqueda" data-previous="" type="text" placeholder="Escriba aquí para filtrar artículos">
		<span id=search class="fas fa-magnifying-glass"></span>
		<?php
			if($horarios->num_rows){
		?>
		<div id="horarios">
		<?php
				while($horario=$horarios->fetch_assoc()){
					echo '<span>Abierto de ';
					$abierto=false;
					$dias=['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
					foreach($dias as $k=>$dia){
						if(!$abierto && (int)$horario[$dia]){
							echo ucwords($dia).' a ';
							$abierto=true;
						}elseif($abierto && !(int)$horario[$dia]){
							echo ucwords($dias[$k-1])." de {$horario['desde']} a {$horario['hasta']}";
							$abierto=false;
							break;
						}
					}
					if($abierto){
						// TODO DRY
						echo "Domingo de {$horario['desde']} a {$horario['hasta']}";
					}
					echo '.</span>';
				}
		?>
		</div>
		<?php
			}
		?>
	</div>
	<div id=content>
<?php

$articulosTable='`pd_articulos`';
$seccionesTable='`pd_secciones`';

function showSeccionChildren($seccionID){
	global $relaciones;
	
	if(isset($relaciones[$seccionID]))
		foreach($relaciones[$seccionID] as $sonID)
			showSeccion($sonID,$seccionID);
}

function showSeccionName($ID,$parentID,$nombre){
	// echo "<h2 data-id=$ID data-parent=$parentID class=categoria-cerrada>$nombre</h2>";
	echo "<h2 data-id=$ID data-parent=$parentID class=\"categoria-cerrada".(((int)$parentID)==0?"":' hidden-by-user')."\">$nombre</h2>";
}

function showArticle($art,$hidden=true){
	echo
		"<div class=\"".($hidden?'hidden-by-user ':'')."articulo\" data-id={$art['ID']} data-serializable='[\"".JSONInHTMLCompatibleString($art['nombre'])."\",\"".JSONInHTMLCompatibleString($art['descripcion'])."\",{$art['codigo_de_barras']}]' data-precio=\"{$art['precio']}\">
			<img loading=\"lazy\" src=\"img/articulo.php?ID={$art['ID']}\" onload=\"build(this)\">
		</div>";
}

function showSeccion($seccionID,$parentID){
	global $secciones,$relaciones;
	
	if(!isset($secciones[$seccionID]))
		return;
	
	$thisSec=$secciones[$seccionID];
	
	showSeccionName($seccionID,$parentID,$thisSec['nombre']);
	
	if(isset($thisSec['articulos']))
		foreach($thisSec['articulos'] as $art)
			showArticle($art);
			
	if(isset($relaciones[$seccionID]))
		showSeccionChildren($seccionID);;
}

function JSONInHTMLCompatibleString($str){
	return str_replace("'","\'",htmlentities($str,ENT_QUOTES));
}

$secciones=[];
$otros=[];
$destacados=[];
$relaciones=[];
$articulos=$db->query("SELECT * FROM $articulosTable WHERE `vendedorID`={$thisVendedor['ID']} AND `disponible`=1");

if($articulos && $articulos->num_rows){
	while($art=$articulos->fetch_assoc()){
		if((int)$art['destacado']){
			$destacados[]=$art;
			continue;
		}
		
		if(!(int)$art['seccionID']){
			$otros[]=$art;
			continue;
		}
		
		if(isset($secciones[$art['seccionID']]))
			$secciones[$art['seccionID']]['articulos'][]=$art;
		else $secciones[$art['seccionID']]=['articulos'=>[$art]];
	}
}

$seccionesRaw=$db->query("SELECT `ID`,`nombre`,`parentID` FROM $seccionesTable WHERE `vendedorID`=".$thisVendedor['ID'].' ORDER BY `nombre`');
if($seccionesRaw && $seccionesRaw->num_rows)
	while($sec=$seccionesRaw->fetch_assoc()){
		if(isset($secciones[$sec['ID']]))
			$secciones[$sec['ID']]['nombre']=$sec['nombre'];
		else $secciones[(int)$sec['ID']]=['nombre'=>$sec['nombre']];
			
		if(isset($relaciones[$sec['parentID']]))
			$relaciones[$sec['parentID']][]=$sec['ID'];
		else $relaciones[$sec['parentID']]=[$sec['ID']];
	}

$changed=true;
$removed=[];

while($changed){
	$changed=false;
	foreach($secciones as $id=>$sec){
		if(!(isset($secciones[$id]['articulos']) || isset($relaciones[$id]))){
			$removed[]=$id;
			unset($secciones[$id]);
			if(!$changed)
				$changed=true;
		}else if(isset($relaciones[$id])){
			$relaciones[$id]=array_udiff($relaciones[$id],$removed,function($a,$b){return $a-$b;});
			if(!count($relaciones[$id])){
				unset($relaciones[$id]);
				if(!$changed)
					$changed=true;
			}
		}
	}
}
	
if(count($destacados)){
	showSeccionName(-1,0,'Destacados');

	foreach($destacados as $art)
		showArticle($art);
}
	
$haySecciones=isset($relaciones[0]);

if($haySecciones){
	showSeccionChildren(0);

	if(count($otros)){
		showSeccionName(0,0,'Otros');
	}
}

// ! Si está vacío, no va a mostrar nada. ¯\_(ツ)_/¯
foreach($otros as $art)
	showArticle($art,$haySecciones);

?>

		<div id=no-article>No hay artículos disponibles por el momento.</div>
		<div style="height: 1px;"></div>
		</div>
		<div id=resumen class=resumen-cerrado>
			<div id="resumen-header">
				<b>Mi pedido</b>
				<div></div>
				<span id=resumen-header-cerrar class=fas>❌</span>
			</div>
			<div id="resumen-grandote">
				<div id="resumen-alerta">La compra mínima es de $&nbsp;<?=$thisVendedor['minimoCompra']?></div>
				<h2>Datos de envío</h2>
				<div id="resumen-grandote-datos">
					<input maxlength=40 id="changuito-nombre" placeholder="Nombre" required>
					<input maxlength=60 id="changuito-domicilio" placeholder="Domicilio" required>
					<input id="changuito-formaDePago" placeholder="Forma de Pago" required>
					<select data-value="<?=$db->query("SELECT precio FROM pd_cambiosdelivery WHERE cuando<now() ORDER BY cuando DESC LIMIT 1")->fetch_assoc()['precio'] ||0?>" id="changuito-retiro">
						<option value="">Forma de retiro</option>
						<option value=1>Delivery</option>
						<option value=0>Pick up</option>
					</select>
				</div>
				<h2>Datos del pedido</h2>
				<div id="resumen-grandote-items"></div>
				<div id=resumen-total>
					<span>Total</span>
					<span id=resumen-grandote-total class=precio>0</span>
				</div>
			</div>
			<div id="resumen-chiquito">
				<img id="resumen-chiquito-img" src="img/changuito.png">
				<span id="resumen-chiquito-cantidad" class="resumen-chiquito-hoarder"></span>
				<span id=resumen-chiquito-titulo></span>
				<div id="resumen-chiquito-precio" class="resumen-chiquito-hoarder"></div>
			</div>
		</div>
	</div>
</body>
</html>