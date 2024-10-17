
var pedidos={},request=0,reclamosPageNum=0,deletingGrupo,grupoID,itemForm;


function openScreen(screenID){
	let prev=SqS('.selected');
	if(prev){
		prev.classList.remove('selected');
	}
	let opened=SqS('#realPanel > *:not(.notSelectedPanel)'),toOpen=gEt(screenID);
	if(opened)
		if(opened==toOpen)
			return;
		else opened.classList.add('notSelectedPanel');
	toOpen.classList.remove('notSelectedPanel');
	toOpen.classList.add('selected');
}

function pedirPedidos(vendedorID,from='',until=''){
	addNode(gEt('ven-pedidos'),['SPAN',{
		class:'noHayNada'
		,innerText:'Cargando...'
	}])

	let thisReques=++request;
	sendJSON('libs/ped/get-admin.php',{ID:vendedorID,from,until})
		.then(res=>res.json())
		.then(thisPedidos=>{
			pedidos[+vendedorID]={
				list:thisPedidos
				,period:[from,until]
				,cuando:+new Date
			};
			if(thisReques==request)
				addPedidos(thisPedidos);
		});
}

function addPedidos(peds){//if you ever add images, loading='lazy'
	let container=gEt('ven-pedidos');
	container.innerHTML='';
	if(peds.length){
		for(let ped of peds){
			let
				div=createNode('DIV',{
					class:'ped'
					,dataset:{id:ped.ID}
				})
				,butt=createNode('I',{
					classList:['ped-toggle','fa','fa-angle-down']
					,dataset:{value:0}
					,onclick:expand
					// ,innerText:'▽'
				})
				,span=createNode('SPAN',{
					class:'ped-name'
					,innerText:ped['titulo']
				})
				,arts=createNode('DIV',{class:'ped-arts'})
				,total=createNode('DIV',{class:'ped-plata'})
				,realTot=0;
			
			for(let titulo of ['Cant.','Artículo','Precio Unitario','Subtotal'])
				arts.append(
					createNode('DIV',{
						innerText:titulo
						,classList:['ped-arts-header']
					})
				);
			for(let art of ped.items){
				let subtNum=(+art.cantidad)*(+art['precioThen'])
					,cant=createNode('DIV',{
						innerText:art.cantidad
						,class:'ped-cantidad'
					})
					,item=createNode('DIV',{
						innerText:art.articulo
					})
					,precio=createNode('DIV',{
						innerText:+art['precioThen']
						,class:'ped-plata'
					})
					,subt=createNode('DIV',{
						class:'ped-plata'
						,innerText:+subtNum
					});
				
				realTot=normalizarNumero(realTot+subtNum);
				
				for(let info of [cant,item,precio,subt])
					arts.appendChild(info);
			}
			for(let i=0;i<3;i++){
				let blankCell=D.createElement('DIV');
				blankCell.classList.add('blank');
				arts.appendChild(blankCell);
			}
			
			total.innerText=realTot;
			arts.appendChild(total);
			
			for(let el of [butt,span,arts])
				div.appendChild(el);
			container.appendChild(div);
		}
	}else container.appendChild(createNode('SPAN',{
		innerText:'No hay pedidos con esas condiciones.'
		,class:'noHayNada'
	}));
}

function normalizarNumero(numero){
	return Math.round(numero * 1000)/1000;
}

function expand(){
	let actualValue=!!+this.dataset.value;
	this.parentNode.lastChild.style.display=actualValue?'none':'grid';
	this.dataset.value=+!actualValue;
}

function cargarMasReclamos(){
	fetch('libs/get-reclamos.php?reclamosPageNum='+reclamosPageNum++)
		.then(res=>res.json())
		.then(reclInfo=>{
			let reclamosBoton=gEt('rec-more');
			for(let reclamo of reclInfo.reclamos)//{
				reclamosBoton.before(createNode('DIV',{children:[
					['DIV',{innerText:'Fecha:'}]
					,['DIV',{innerText:reclamo.fecha}]
					,['DIV',{innerText:'Objeto:'}]
					,['DIV',{innerText:reclamo.objeto}]
					,['DIV',{
						// classList:['rec-body'],
						innerText:'\u201C'+reclamo.reclamo+'\u201D'
					}]
				]}))
			//}
			if(!reclInfo.hayMas)
				reclamosBoton.style.display='none';
		});
	
}

function lastMonthPeriod(){
	let today=(new Date).toLocaleDateString('en-CA')
		,todayExploded=today.split('-')
		,mes=todayExploded[1]-1;
	if(mes<10)
		if(mes==0)
			mes=12;
		else mes='0'+mes;
	return [todayExploded[0]+'-'+mes+'-'+todayExploded[2],today];
}

function search(queryString){
	for(let boton of gEt('ven-list-buttons').children){
		let isHidden=boton.classList.contains('hidden');
		if(test(queryString,boton.innerText)){
			if(isHidden)
				boton.classList.remove('hidden');
		}else if(!isHidden)
			boton.classList.add('hidden');
	}
}

function test(queryString,artText){
	return new RegExp('.*?'+queryString+'.*?','i').test(artText);
}

var searchID=0;

function startSearch(valor,reiniciar,buscar=true){
	if(reiniciar){
		for(let boton of [...SqS('.hidden',ALL)])
			boton.classList.remove('hidden');
		searchID++;
		return true;
	}else if(buscar){
		let myID=++searchID;
		setTimeout(()=>{
			if(searchID==myID)
				search(valor);
		},350);
	}
}

function download(URL, nombre){
	let anchor=B.appendChild(createNode('A',{
		href:URL
		,download:nombre
		,class:'hidden'
	}));
	anchor.click();
	anchor.remove();
}

//grupos
function abrirMenuGrupo(event){
	event.stopPropagation();
	const grupoDiv= this.parentNode;
	if(this.classList.contains('grupo-enProceso'))
		return;
	let thisName=this.parentNode.childNodes[0].textContent.trim();
	showOptionsMessage('¿Qué desea hacer con el grupo "'+thisName+'"?'
		//,['Editar',1]
		,['Eliminar',2]
		,['Cancelar',0]
	)
		.then(eleccion=>{
			switch(+eleccion){
			case 0:
				this.classList.remove('grupo-enProceso');
				break;
			/*case 1:
				let newName=prompt('Ingrese un nuevo nombre:',thisName);
				if(!newName || (newName=newName.trim())==thisName)
					return;
				sendJSON('libs/cat/edit.php',{newName,ID:this.dataset.id})
					.then(res=>res.text())
					.then(res=>{
						this.classList.remove('grupo-enProceso');
						if(+res)
							this.innerText=newName;
						else throw new Error('Backend answered with '+res);
					})
					.catch(e=>{
						console.log(e);
						showMessage('Ha ocurrido un error inesperado, vuelva a intentar más tarde.','red');
					});
				break;*/
			case 2:
				if(!confirm('¿Seguro desea eliminar el grupo '+thisName+'?\nEsta acción no se puede revertir.')){
					this.classList.remove('grupo-enProceso');
					return;
				}
				let thisID=this.dataset.id
				sendJSON('libs/grupos/has-arts.php',{ID:thisID})
					.then(res=>res.text())
					.then(cant=>{
						if(+cant){
							showMessage('No se puede eliminar el grupo '+thisName+' ya que contiene '+cant+(cant==1?'artículo':'artículos'));
						}else sendJSON('libs/grupos/has-vends.php',{ID:thisID})
							.then(res=>res.text())
							.then(cant=>{
								if(+cant){
									gEt('grupo-hasVends-body-cuantos').innerText=cant+' vendedor'+(cant==1?'':'es');
									gEt('grupo-hasVends').style.display='flex';
									deletingGrupo=thisID;
								}else sendJSON('libs/grupos/delete-grup.php',{ID:thisID})
									.then(res=>res.text())
									.then(res=>{
										if(res){
											showMessage('El grupo se ha eliminado satisfactoriamente.','limegreen');
											grupoDiv.remove(); 
											let container=gEt('grupos-container');
											if(! SqS('.grupo'))	//si no quedan más grupos
												container.innerHTML='<p>No existen grupos de artículos aún.</p>';
										}else if(! +res) throw new Error('Backend answered with '+res);
									})
							.catch(e=>{
								this.classList.remove('grupo-enProceso');
								console.log(e);
							});
					})});
				break;
			}
		})
}
function abrirGrupo() {
	openScreen('grupo');
	let grupo=this;
	sendJSON('libs/grupos/get.php',{grupoID:grupo.dataset.id})
	.then(res=>res.json()).
	then(
		data=>{
			gEt('titulo-grupo').innerText=data.nombre;
		}
	);
	//variable para manejar la panatalla de grupo
	grupoID=grupo.dataset.id;
}

function abrirEditor(artID=0,secID=0){
	itemForm.previousElementSibling.classList.add('notSelectedPanel');
	itemForm.classList.remove('notSelectedPanel');
	editing=artID;//necessary for submit, editing is global
	// let eliminarButton=gEt('art-eliminar');
	if(artID){
		let thisArt=articulos[artID];
		for(let campo of [
			['img','src','img/articulo.php?ID='+artID+'&rand='+Math.random()]
			,['[name="Nombre"]','defaultValue',thisArt['nombre']]
			,['[name="Descripcion"]','value',thisArt['descripcion']]
			// ,['[name="Disponible"]','value',thisArt['disponible']]
			,['[name="Precio"]','value',thisArt['precio']]
			,['#new-art-sec-input','value',thisArt['seccionID']]
			,['[name="codigo"]','value',thisArt['codigo_de_barras']]
		]){
			let campoElem=SqS(campo[0],ONLY_ONE,itemForm);
			campoElem[campo[1]]=campo[2];
			if(campo[1]=='value')
				campoElem.defaultValue=campo[2];
		}
		// gEt('art-añadir-destacado').firstElementChild.checked=!!+thisArt.destacado;
		// if(eliminarButton.hasAttribute('style'))
		// 	eliminarButton.removeAttribute('style');
	}else{
		resetArtForm();
		let select=gEt('new-art-sec-input');
		select.value=secID;
		// if(!eliminarButton.hasAttribute('style'))
		// 	eliminarButton.style.display='none';
	}
	gEt('art-subir-boton').value=artID?'Actualizar Artículo':'Subir Artículo';
}
function resetArtForm(){
	let form=D.forms['art-añadir'];
	form.Nombre.defaultValue='';
	form.Descripcion.defaultValue='';
	form.Precio.defaultValue='';
	form.codigo.defaultValue='';
	form.reset();
	form.foto.previousElementSibling.src='img/articulo.php';
	form.foto.nextElementSibling.classList.add('hidden');
}

function crearArticulos(...rawArticulos){
	let artGrid=gEt('art-grid');
	if(artGrid.firstChild && artGrid.firstChild.tagName=='H2')
		artGrid.firstChild.remove();
	for(let artObject of rawArticulos){
		artObject.descripcion=artObject.descripcion.replaceAll('\\n','\n');
		articulos[artObject.ID]=artObject;
		
		let seccionName=secciones[artObject.seccionID],
			whole=createNode(
				'DIV',{
					class:'articulo'
					,dataset:{
						id:artObject.ID
						,info:artObject.nombre+','+artObject.descripcion+','+artObject.codigo_de_barras+','+seccionName
					}
					,children:[
						['SPAN',{class:'art-symbol'}]
						,['SPAN',{class:'art-nombre',innerText:artObject.nombre}]
						,['I',{classList:['fas','fa-ellipsis-h']}]
						,['SPAN',{children:[
							['SPAN',{class:'art-precio',innerText:artObject.precio}]
							,['SPAN',{children:[
								['SPAN',{innerText:' | '}]
								,['SPAN',{innerText:seccionName}]
							]}]
						]}]
					]
				}
			);
		if(artObject.disponible==2)
			whole.classList.add('escondido');
		if(+artObject.destacado)
			whole.classList.add('destacado');
		artGrid.appendChild(whole);
	}
}

//errores:
function genericCatch(e){
	console.log(e);
	noSeHaPodido();
}
function noSeHaPodido(){
	alert('No se ha podido realizar la acción, reintente más tarde.');
}
addEventListener('DOMContentLoaded',()=>{
	itemForm=gEt('art-añadir');
	//events
	
	gEt('anvorgesa').onclick=function(e){
		let target=e.target;
		if(target==this)
			this.classList.remove('anvorgesa-abierta');
		if(target.id=='salir')
			return;
		if(target.tagName=='IMG')
			target=target.parentNode;
		target=target.closest('button');
		let classList=target.classList;
		if(!classList.contains('selected')){
			let screenID;// TODO =target.value
			switch(+target.value){
			case 1:
				screenID='ven';
				break;
			case 2:
				screenID='grupos';
				screenID='grupos';
				break;
			case 3:
				screenID='rec';
				break;
			case 4:
				screenID='cue';
				break;
			}
			openScreen(screenID);
			openScreen(screenID);
			this.classList.remove('anvorgesa-abierta');
		}
	}
	
	gEt('salir').onclick=async()=>{
		await fetch('libs/logout.php');
		W.location='/login.php';
	};
	
	gEt('ven-list-buttons').onclick=e=>{
		let target=e.target;
		if(target.tagName=='BUTTON'){
			let habilitado=gEt('habilitado')
				,from=gEt('from')
				,until=gEt('until');
				
			gEt('ven-nombre').innerText=target.innerText;

			// TODO Refactor: Mejorar esto...
			gEt('ven-buscar').disabled = gEt('ven-exportar').disabled = false;

			habilitado.checked=!!+target.dataset.habilitado;
			habilitado.disabled=false;
			habilitado.value=target.value;
			from.disabled=false;
			until.disabled=false;
			let thisPedido=pedidos[+target.value];
			if(!thisPedido || thisPedido < (+new Date)-3600000){
				pedirPedidos(
					target.value
					,...([from.value,until.value]=thisPedido?thisPedido.period:lastMonthPeriod())
				);
			}else{
				[from.value,until.value]=thisPedido.period;
				addPedidos(thisPedido.list);
			}
		}
	}
	
	gEt('ven-buscar').onclick=()=>{
		let checkbox=gEt('habilitado');
		if(checkbox.value!='on')
			pedirPedidos(checkbox.value,gEt('from').value,gEt('until').value);
	}
	
	gEt('ven-exportar').onclick=()=>{
		let vendedorID=gEt('habilitado').value;
		if(vendedorID!='on'){
			showOptionsMessage(
				'¿Qué desea exportar?'
				,['Todo el Historial',2]
				,['La Tabla',1]
				,['Cancelar',0]
			)
				.then(res=>{
					let IDs='';
					switch(+res){
					case 1:
						IDs=[...gEt('ven-pedidos').children].map(el=>el.dataset.id);
					case 2:
						download('libs/ped/export.php?vendedorID='+gEt('habilitado').value+'&IDs='+IDs,'pedidos.xlsx');
						break;
					}
				});
		}
	}
	
	gEt('habilitado').onchange=function(){
		this.disabled=true;
		sendJSON('libs/toggle-habilitacion.php',{vendedorID:this.value,hab:+this.checked})
			.then(res=>{
				if(!res.ok){
					this.checked=!this.checked;
					alert('Ha ocurrido un problema.');
				}else SqS('#ven-list-buttons button[value="'+this.value+'"]').dataset.habilitado = +this.checked;
				this.disabled=false;
			});
	}
	
	gEt('cue-name-update').onclick=function(){
		let input=this.previousElementSibling;
		if(input.value==input.defaultValue)
			return;
		input.disabled=true;
		let value=input.value.trim();
		sendJSON('libs/admin/update-name.php',{name:value})
			.then(res=>res.text())
			.then(response=>{
				if(+response){
					input.defaultValue=value;
					gEt('nombre').innerText=value;
				}else throw new Error('Backend said '+response);
			})
			.catch(e=>{
				console.log(e);
				showMessage('Ha ocurrido un error inesperado. Intente más tarde.','red');
			})
			.finally(()=>input.disabled=false);
	}

	gEt('cue-pass').onclick=()=>{
		let newPassword=prompt('Ingrese nueva contraseña:').trim();
		if(newPassword){
			let newPasswordAgain=prompt('Vuelva a ingresar la nueva contraseña:').trim();
			if(newPassword==newPasswordAgain){
				let oldPassword=prompt('Ingrese su contraseña actual:').trim();
				this.disabled=true;
				sendJSON('libs/admin/new-password.php',{newPassword,oldPassword})
					.then(res=>res.text())
					.then(response=>{
						switch(+response){
						case -1:
							showMessage('Ocurrió un error, intente más tarde.','#F00');
							break;
						case 0:
							showMessage('Contraseña incorrecta.','#F00');
							break;
						case 1:
							showMessage('Se ha actualizado la contraseña','#0F0');
							break;
						}
					})
					.catch(e=>{
						console.log(e);
						alert('No se ha podido realizar la acción, reintente más tarde.');
					})
					.finally(()=>this.disabled=false);
			}else showMessage('Las contraseñas ingresadas no coinciden, intentelo otra vez.');
		}
	};
	
	let listFilter=gEt('ven-list-filter')
	
	listFilter.onkeyup=function(e){
		let realValue=this.value.trim();
		startSearch(
			realValue
			,!realValue && e.which==8
			,e.key.length==1 || e.which==8
		);
	};
	
	listFilter.onchange=function(){
		let realValue=this.value.trim();
		startSearch(
			realValue
			,!realValue
		);
	};
	
	//UI building
	
	fetch('libs/get-vendedores.php')
		.then(res=>res.json())
		.then(vends=>{
			if(vends.length){
				let container=gEt('ven-list-buttons');
				for(let vend of vends)
					container.appendChild(createNode('BUTTON',{
						value:vend['ID']
						,innerText:vend['nombre']
						,dataset:{habilitado:vend['habilitado']}
					}));
			}
		});
		
	gEt('rec').append(createNode('BUTTON',{
		id:'rec-more'
		,innerText:'Cargar más reclamos'
		,onclick:cargarMasReclamos
	}));
	
	cargarMasReclamos();
	
	//grupos
	fetch('libs/grupos/get-all.php')
		.then(res=>res.json())
		.then(grupos=>{
			let container=gEt('grupos-container');
			if(grupos.length){
				for(let grupo of grupos){
					let grupo_div = createNode('DIV',{
						class:'grupo'
						,innerText:grupo['nombre']
						,onclick:abrirGrupo
						,dataset:{id:grupo['ID']}
					});
					grupo_div.appendChild(createNode('BUTTON',{
						class:"grupo-edit",
						onclick:abrirMenuGrupo,
						innerText:'⋮',
						dataset:{id:grupo['ID']}
					}));
					container.appendChild(grupo_div);
				}
			}else{
				container.innerHTML='<p>No existen grupos de artículos aún.</p>';
			}
		})
	
	gEt('grupo-add').onclick=()=>{
		let nuevoNombre=prompt('Ingrese el nombre del nuevo grupo.');
		let comision=prompt('Ingrese la comision del nuevo grupo.');
		
		if((nuevoNombre && (nuevoNombre=nuevoNombre.trim()))&& !(isNaN(comision) || comision === '')){
			sendJSON('libs/grupos/add.php',{nombre:nuevoNombre,comision:comision})
				.then(res=>res.text())
				.then(res=>{
					if(+res){
						showMessage('El grupo se ha creado satisfactoriamente.','limegreen');

						let newGrupo=createNode('DIV',{
							classList:['grupo','grupo-enProceso']
							,innerText:nuevoNombre
							,onclick:abrirGrupo
							,dataset:{id:res}
						});
						newGrupo.appendChild(createNode('BUTTON',{
							class:"grupo-edit"
							,onclick:abrirMenuGrupo
							,innerText:'⋮'
							,dataset:{id:res}
						}));
						let container=gEt('grupos-container');
						container.appendChild(newGrupo);
						const gruposVacío = container.querySelector('p');
						if(gruposVacío)
							gruposVacío.remove();
						newGrupo.classList.remove('grupo-enProceso');
					}else throw new Error('Backend answered with '+res);
				})
				.catch(e=>{
					console.log(e);
					showMessage('Ha ocurrido un error, intente más tarde.\nSi este error le ocurre seguido, contacte al soporte.','red');
				});
		}
	}
	
	gEt('grupo-hasVends').onclick=function(e){
		if(e.target==this)
			this.firstElementChild.lastElementChild.lastElementChild.click();//cancelar button
	}
	
	gEt('grupo-hasVends-body-buttons').onclick=function(e){
		let target=e.target;
		if(target==this)
			return;
		
		gEt('grupo-hasVends').style.display='none';
		if(+target.value){
			let grupoToKill=SqS('.grupo[data-id="'+deletingGrupo+'"]');
			grupoToKill.classList.add('grupo-enProceso');
			sendJSON('libs/grupos/delete-grup.php',{ID:deletingGrupo})
				.then(res=>res.text())
				.then(res=>{
					if(res){
						showMessage('El grupo se ha eliminado satisfactoriamente.','limegreen');
						grupoToKill.remove(); 
						let container=gEt('grupos-container');
					if(! SqS('.grupo'))	//si no quedan más grupos
						container.innerHTML='<p>No existen grupos de artículos aún.</p>';
				}else if(! +res) throw new Error('Backend answered with '+res);
			})
				.catch(e=>{
					grupoToKill.classList.remove('grupo-enProceso');
					console.log(e);
					showMessage('Ha ocurrido un error, intente de nuevo más tarde.\nSi ocurre seguido, contacte con el soporte.','red');
				});
		}
		deletingGrupo=null;
	}
	//grupo
	/* ex botones de articulos en vendedor: 
	gEt('art-inicio').onclick=function(e){
		let target=e.target;
		if(target==this)
			return;
		if(target.tagName=='BUTTON' && target.classList.contains('new')){
			let artGrid=gEt('art-grid');
			
			if(target.classList.contains('sec')){
				let nombre=prompt('Escriba el nombre de la nueva categoría:');
				if(nombre && (nombre=nombre.trim()))
					sendJSON('libs/new-sec.php',{nombre,parentID:0})
						.then(res=>res.json())
						.then(crearSec);

			}else if(target.classList.contains('art'))
				abrirEditor(0,0);
			
			else if(target.id=='bulk')
				gEt('bulk-message').style.display='flex';
	
		}else if(target.id=='export'){
			this.classList.add('art-exporting');
			target.style.display='none';
			target.nextElementSibling.style.display='grid';
		}else if(this.classList.contains('art-exporting')){
			let closestArt=target.closest('.articulo');
			if(closestArt)
				closestArt.classList.toggle('art-exporting-selected');
		}else if(target.classList.contains('fa-ellipsis-h')){
			
			let closestArt=target.closest('.articulo')
				,boundingBox=closestArt.getBoundingClientRect();
			showArtsContextMenu(boundingBox.right-boundingBox.width+15,boundingBox.top+15)
				.then(boton=>{
					switch(boton){
					case 'Modificar':
						if(!this.classList.contains('art-exporting') && !closestArt.classList.contains('nominado'))
							abrirEditor(closestArt.dataset.id);
						break;
					case 'Destacar':
						toggleArtAttr('destacado','destacado',closestArt);
						break;
					case 'Ocultar':
						toggleArtAttr('oculto','escondido',closestArt);
						break;
					case 'Eliminar':
						showOptionsMessage(
							'¿Está seguro de que desea hacer esto? Esta acción no se puede deshacer.'
							,['Proceder',1]
							,['Cancelar',0]
						)
							.then(res=>{
								if(+res){
									// let artEnCuestion=SqS('.articulo[data-id="'+editing+'"]');
									closestArt.classList.add('nominado');
									sendJSON('libs/art/delete.php',{ID:closestArt.dataset.id})
										.then(res=>res.text())
										.then(res=>{
											if(+res){
												closestArt.remove();
												delete articulos[closestArt.dataset.id];
											}else closestArt.classList.remove('nominado');
										})
								}
							});
					default:
						return;
					}
				});
		}
	}*/
	
	D.forms['art-añadir'].onsubmit=function(){
		let campos={
			grupoID:grupoID
			,nombre:this.Nombre
			// ,dispo:this.Disponible
			// ,destacado:+this.destacado.checked
			,descripcion:this.Descripcion
			,codigo:this.codigo
			,precio:this.Precio
			,foto:this.foto
			,seccion:this.Seccion
		},url,body,after,parameters;
		
		if(editing){
			url='libs/art/edit.php';
			body=[];
			
			body.push(['editing',editing]);
			for(let campo of [
				'nombre'
				,'descripcion'
				,'precio'
				,'codigo'
			]){//campos con defaultValue
				let thisCampo=campos[campo];
				if(thisCampo.defaultValue!=thisCampo.value)
					body.push([campo,thisCampo.value]);
			}
			for(let campo of [
				// ['disponible','dispo'],
				['seccionID','seccion']
			]){//campos sin defaultValue
				let valorCampoReal=campos[campo[1]].value;
				if(articulos[editing][campo[0]]!=valorCampoReal)
					body.push([campo[1],valorCampoReal]);
			}
			// if(campos.destacado != articulos[editing]['destacado'])
			// 	body.push(['destacado',campos.destacado]);
				
			
			if(campos.foto.files.length){
				let fd=new FormData;
				for(let [key,value] of body)
					fd.append(key,value);
				fd.append('foto',campos.foto.files[0]);
				parameters=['ajax',{body:fd}];
			}else{
				if(body.length>1){
					let newBody={};
					for(let [key,value] of body)
						newBody[key]=value;
					parameters=['sendJSON',newBody];
				}else{
					gEt('art-añadir-volver').click();
					return false;
				}
			}
			
			after=response=>{
				let editingArt=SqS('.articulo[data-id="'+editing+'"]')
					,datos=articulos[editing]
					,updateInfo=({n=datos.nombre,d=datos.descripcion,c=datos.codigo_de_barras,s=secciones[datos.seccionID]})=>{
						// let datos=articulos[editing];
						// editingArt.dataset.info=datos.nombre+','+datos.descripcion+','+datos.codigo_de_barras+','+s
						editingArt.dataset.info=`${n},${d},${c},${s}`;
						// let info=editingArt.dataset.info.split(',');
					};
				for(let key of Object.keys(response))
					switch(key){
					case 'updateName':
						let newName=campos.nombre.value;
						editingArt.children[1].innerText=newName;
						articulos[editing].nombre=newName;
						updateInfo({n:newName});
						// campos.nombre.defaultValue=newName;
						break;
					case 'updateDesc':
						// let newDesc
						updateInfo({d:
						articulos[editing].descripcion
						=campos.descripcion.value//;
							// newDesc
						});
						// =newDesc;
						// campos.descripcion.defaultValue=newDesc;
						break;
					case 'updateCode':
						updateInfo({
							c:articulos[editing].codigo_de_barras=campos.codigo.value
						});
						break;
					case 'updatePrecio':
						let newPrecio=campos.precio.value;
						articulos[editing].precio=newPrecio;
						editingArt.lastElementChild.firstElementChild.innerText=newPrecio;
						break;
					case 'updateSec':
						let newSec=campos.seccion.value;
						articulos[editing].seccionID=newSec;
						editingArt.lastElementChild.lastElementChild.lastElementChild.innerText=secciones[newSec];
						updateInfo({n:newSec});
						break;
					}
					
				this.firstElementChild.disabled=false;
			};
		}else{//new 
			if(!campos.foto.files.length){
				alert('Debe elegir una imagen para el artículo nuevo.');
				return false;
			}
			url='libs/art/new.php';
			body=new FormData(this);
			body.append('editing',0);
			after=response=>{
				crearArticulos(response);
				
				this.firstElementChild.disabled=false;
			}
			parameters=['ajax',{body:body}];
		}
		
		this.firstElementChild.disabled=true;
		
		W[parameters[0]](url,parameters[1])
			.then(r=>r.json())
			.then(response=>{
				after(response);
				gEt('art-añadir-volver').click();//volver
				resetArtForm();
			})
			.catch(e=>{
				this.firstElementChild.disabled=false;
				genericCatch(e);
				// console.log(e);
				// noSeHaPodido();
			})
			;
			
		return false;
	};
	
});