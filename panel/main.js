
var articulos={}
	,editing=0
	,itemForm
	,requestNro=0
	,finishedArts=false
	,finishedSecs=false
	,spinners
	,pedidosPage=0
	// ,chosenToExport=[]
	,secciones
	,thatColor='#369683'
	;



function genericCatch(e){
	console.log(e);
	noSeHaPodido();
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

function changeImage(e){
	if (this.files && this.files[0]) {
		const FR= new FileReader;
		FR.addEventListener("load", e=>{
			this.previousElementSibling.src=e.target.result;
			this.nextElementSibling.classList.remove('hidden');
		});
		FR.readAsDataURL( this.files[0]);
	}
}

function resetImagePicker(button,src){
	button.previousElementSibling.previousElementSibling.src=src;
	button.previousElementSibling.value='';
	button.classList.add('hidden');
}

function noSeHaPodido(){
	alert('No se ha podido realizar la acción, reintente más tarde.');
}

function closeMeAndOpen(me,open){
	me.classList.add('notSelectedPanel');
	open.classList.remove('notSelectedPanel');
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

function normalizarNumero(numero){
	return Math.round(numero * 100000)/100000;
}

function openPedido(){
	let [div,text]= +this.value?
		['none','▽']
		:['block','△'];
	this.parentNode.nextElementSibling.style.display=div;
	this.innerText=text;
	this.value=+!+this.value;
}



function deleteFun(){
	let botones=[]
		,parent=this.parentNode
		,thisSeccion=parent.parentNode
		,reestablecerBotones=()=>botones.map(boton=>boton.disabled=false);
	for(let child of parent.children)
		if(child.tagName=='BUTTON'){
			child.disabled=true;
			botones.push(child);
		}
	if(confirm('¿Seguro desea eliminar esta sección? Esto no se puede deshacer. Todos los artículos pasaran a la sección superior o al Inicio.'))
		sendJSON('libs/delete-seccion.php',{secID:thisSeccion.dataset.id})
			.then(response=>{
				if(response.ok){
					let superiorChildrenHolder=thisSeccion.parentNode
					
						,newNeighborsSec=[]
						,newNeighborsArt=[]
						
						,nestedSec=[]
						,nestedArt=[];
					
					for(let sibling of superiorChildrenHolder.children)
						if(sibling.classList.contains('articulo'))
							newNeighborsArt.push(sibling);
						else if(sibling.dataset.id==thisSeccion.dataset.id)
							continue;
						else if(sibling.classList.contains('seccion'))
							newNeighborsSec.push(sibling);
					newNeighborsSec.push(false);
					newNeighborsArt.push(false);
					
					for(let child of parent.nextElementSibling.children)
						if(child.classList.contains('articulo'))
							nestedArt.push(child);
						else if(child.classList.contains('seccion'))
							nestedSec.push(child);
					
					if(newNeighborsSec.length > 1)
						for(let sec of nestedSec)
							for(let i in newNeighborsSec){
								let currentNeighbor=newNeighborsSec[i];
								if(currentNeighbor){
									if(+currentNeighbor.dataset.id > +sec.dataset.id){
										currentNeighbor.before(sec);
										newNeighborsSec.splice(+i,0,sec);
										break;
									}
								}else{
									newNeighborsSec[i-1].after(sec);
									newNeighborsSec.splice(+i,0,sec);
								}
							}
					else for(let sec of nestedSec)
						superiorChildrenHolder.prepend(sec);
					
					if(newNeighborsArt.length)
						for(let art of nestedArt)
							for(let i in newNeighborsArt){
								let currentNeighbor=newNeighborsArt[i];
								if(currentNeighbor){
									if(+currentNeighbor.dataset.id > +art.dataset.id){
										currentNeighbor.before(art);
										newNeighborsArt.splice(+i,0,art);
										break;
									}
								}else{
									newNeighborsArt[i-1].after(art);
									newNeighborsArt.splice(+i,0,art);
								}
							}
					else for(let art of nestedArt)
						superiorChildrenHolder.appendChild(art);
					
					for(let spinner of spinners)
						SqS('[value="'+thisSeccion.dataset.id+'"]',ONLY_ONE,spinner).remove();
					thisSeccion.remove();
				}else{
					reestablecerBotones();
					noSeHaPodido();
				}
			});
	else reestablecerBotones();
}

function openFun(){
	if(this.parentNode.nextElementSibling.children.length){
		let [div,text]=+this.value?['none','▽']:['block','△'];
		this.parentNode.nextElementSibling.style.display=div;
		this.innerText=text;
		this.value=+!+this.value;
	}
}

function editField(e){
	let field=this.parentNode.firstChild;
	if('✏️'==this.innerText){
		this.innerText='✅';
		this.previousElementSibling.style.display='inline';
		field.disabled=false;
		field.focus();
		
		this.parentNode.children[3].style.display='none';
		this.parentNode.children[4].style.display='none';
	
	}else{
		field.disabled=true;
		this.previousElementSibling.style.display='none';
		this.innerText='✏️';
		let newValue=field.value;
		
		this.parentNode.children[3].style.display='block';
		this.parentNode.children[4].style.display='block';
		
		if(field.defaultValue!=newValue){
			this.disabled=true;
			sendJSON('libs/edit-seccion.php',{secID:field.closest('.seccion').dataset.id,newName:newValue})
				.then(response=>{
					if(response.ok){
						field.defaultValue=newValue;
						this.disabled=false;
					}else noSeHaPodido();
				})
				;
		}
	}
}

function cancelEdition(){
	let field=this.parentNode.firstChild;
	field.value=field.defaultValue;
	field.disabled=true;
	this.style.display='none';
	this.nextElementSibling.innerText='✏️';
	
	this.parentNode.children[3].style.display='block';
	this.parentNode.children[4].style.display='block';
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

function crearSec(sec){
	
	for(let spinner of spinners)
		spinner.appendChild(createNode(
			'OPTION',{
				value:sec.ID
				,innerText:sec.nombre
			}
		));
	
	secciones[sec.ID]=sec.nombre;
}



function fetchPedidos(page){
	fetch('libs/ped/get.php?offset='+page)
		.then(r=>r.json())
		.then(response=>{
			if(response.pedidos.length){
				let pedidosChildren=gEt('pedidos').children
					// ,pedidosNuevos=pedidosChildren[1].firstElementChild
					// ,historial=
					;
					//check necessity of these variables
				
				for(let pedido of response.pedidos){
					let parent,total=0,pedidoObject=['DIV',{
						class:'ped-individual'
						,dataset:{id:pedido.ID}
						,children:[
							['SPAN',{
								class:'ped-individual-time'
								,innerText:pedido.time
							}]
							,['SPAN',{
								class:'ped-individual-titulo'
								,innerText:'Datos del cliente'
							}]
							,['SPAN',{
								class:'ped-individual-dato'
								,innerText:'Nombre: '+pedido.nombre
							}]
							,['SPAN',{
								class:'ped-individual-dato'
								,innerText:'Direccion: '+pedido.direccion
							}]
							// ,['SPAN',{
							// 	class:'ped-individual-dato'
							// 	,innerText:'Teléfono: '+pedido.telefono
							// }]
							,['SPAN',{
								class:'ped-individual-titulo'
								,innerText:'Datos del pedido'
							}]
							,['DIV',{
								class:'ped-contents'
								,children:(()=>{
									let arr=[];
									
									for(let item of pedido.items){
										let subtotalNumero=normalizarNumero((+item.precioThen)*(+item.cantidad));
										arr.push(['SPAN',{innerText:`• ${item.cantidad} x ${item.articulo}`}]);
										arr.push(['SPAN',{innerText:`$\u00a0${item.precioThen}`}]);
										total+=subtotalNumero;
									}
									
									


									return arr;
								})()
							}]
							,['SPAN',{
								class:'ped-individual-titulo'
								,innerText:'Total: $ '+total
							}]
						]
					}];
					
					;
					
					
					if(pedido.esNuevo){
						pedidoObject[1].children.push(['DIV',{
							class:'ped-individual-buttons'
							,children:[
								['BUTTON',{
									value:0
									,classList:['cute-button','ped-individual-buttons-0']
									,children:[
										['I',{classList:['fas','fa-thumbs-down']}]
										,['SPAN',{innerText:'Rechazado'}]	
									]
								}]
								,['BUTTON',{
									value:1
									,classList:['cute-button','ped-individual-buttons-1']
									,children:[
										['I',{classList:['fas','fa-thumbs-up']}]
										,['SPAN',{innerText:'Enviado'}]	
									]
								}]
							]
						}]);
						addNode(pedidosChildren[1].firstElementChild,pedidoObject)
					}else addNode(pedidosChildren[2].children[1],pedidoObject);
				}
			}
			
			if(!+response.hayMas)
				gEt('ped-mas').style.display='none';
			
		});
}

//buscador

var searchID=0;

function search(queryString){
	for(let articulo of SqS('.articulo',ALL)){
		let isHidden=articulo.classList.contains('hidden');
		if(test(queryString,articulo.dataset.info)){
			if(isHidden){
				articulo.classList.remove('hidden');
				
				let seccionHolder=articulo,i=0;
				
				while(seccionHolder=seccionHolder.closest('.seccion.hidden'))
					seccionHolder.classList.remove('hidden');
			}
		}else if(!isHidden){
			articulo.classList.add('hidden');
			let currentSeccion=articulo.parentNode;
			
			if(currentSeccion.id!='art-grid')
				do{
					currentSeccion=currentSeccion.parentNode;
					if(!(SqS('.articulo:not(.hidden)',ALL,currentSeccion).length))
						currentSeccion.classList.add('hidden');
				}while((currentSeccion=currentSeccion.closest('.children-holder')));
		}
	}
}

function test(queryString,artText){
	return new RegExp('.*?'+queryString+'.*?','i').test(artText);
}

function startSearch(valor,reiniciar,buscar=true){
	if(reiniciar){
		for(let hidden of [...SqS('.hidden',ALL)])
			hidden.classList.remove('hidden');
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

function volverDelEditor(){
		// this.parentNode.parentNode
	gEt('art-añadir').classList.add('notSelectedPanel');
	gEt('art-inicio').classList.remove('notSelectedPanel');
}


function bulkInputsOnchange(){
	if(!(this.files && this.files[0]))
		return;
		
	let cLToEdit=this.nextElementSibling.classList;
	if(cLToEdit.contains('bulk-image-done'))
		cLToEdit.remove('bulk-image-done');
	if(cLToEdit.contains('bulk-image-fail'))
		cLToEdit.remove('bulk-image-fail');
	cLToEdit.add('emoji-loader');
	
	let fd=new FormData()
		,editingID=this.closest('.bulk-image-input').dataset.newid;
	fd.append('newImage',this.files[0]);
	fd.append('ID',editingID);
	ajax('libs/update-art-img.php',{body:fd})
		.then(res=>res.text())
		.then(response=>{
			cLToEdit.remove('emoji-loader');
			if(+response){
				cLToEdit.add('bulk-image-done');
				SqS('.articulo[data-id="'+editingID+'"]').firstChild.src='img/articulo.php?ID='+editingID+'&rand='+Math.random();
			}else cLToEdit.add('bulk-image-fail');
		})
		.catch(e=>{
			console.log(e);
			cLToEdit.remove('emoji-loader');
			cLToEdit.add('bulk-image-fail');
		});
};

function download(URL, nombre){
	let anchor=B.appendChild(createNode('A',{
		href:URL
		,download:nombre
		,class:'hidden'
	}));
	anchor.click();
	anchor.remove();
}

function showArtsContextMenu(nx,y){
	let contextMenu=gEt('art-context');
	
	contextMenu.firstElementChild.style.right=nx+'px';
	contextMenu.firstElementChild.style.top=y+'px';
	
	contextMenu.style.display='flex';
	return new Promise((resolve,reject)=>{
		contextMenu.onclick=function(e){
			let target=e.target;
			switch(target.tagName){
			case 'BUTTON':
				resolve(target.innerText);
				break;
			case 'I':
				resolve(target.parentNode.innerText);
				break;
			default:
				reject();
			}
			this.style.display='none';
		}
	});
}

function toggleArtAttr(attr,CSSclass,art){
	art.classList.add('nominado');
	art.classList.toggle(CSSclass);
	sendJSON(`libs/art/toggle/${attr}.php`,{ID:art.dataset.id,value:art.classList.contains(CSSclass)})
		.then(res=>res.text())
		.then(text=>{
			if(+text)
				art.classList.remove('nominado');
			else art.classList.toggle(CSSclass);
		})
		.catch(genericCatch);
}

addEventListener('DOMContentLoaded',()=>{
	
	itemForm=gEt('art-añadir');
	spinners=[SqS('[name="Seccion"]',ONLY_ONE,itemForm),gEt('bulk-area-select')];
	
	//onclicks / onchanges
	
	gEt('anvorgesa').onclick=async function(e){
		let target=e.target;
		if(target==this)
			this.classList.remove('anvorgesa-abierta');
		else{
			switch(target.tagName){
			case 'SPAN':
			case 'I':
				target=target.parentNode;
				break;
			case 'BUTTON':
				break;
			default:
				return;
			}
			if(target.id=='salir'){
				await fetch('libs/logout.php');
				W.location='login.php';
				return;
			}
			let classList=target.classList;
			if(!classList.contains('selected')){
				let prev=SqS('.selected');
				if(prev)
					prev.classList.remove('selected');
				classList.add('selected');
				let opened=SqS('#content > *:not(.notSelectedPanel)')
					,toOpen=gEt(target.value);
				if(opened){
					if(opened==toOpen)
						return;
					else opened.classList.add('notSelectedPanel');
				}
				toOpen.classList.remove('notSelectedPanel');
				this.classList.remove('anvorgesa-abierta');
			}
		}
	};
	
	[...SqS('.imagePicker input',ALL)].map(e=>e.onchange=changeImage);
	
	let colorField=SqS('[type="color"]',ONLY_ONE,perfil)
		,reestablecerColor=gEt('reestablecer');
	reestablecerColor.onclick=function(){
		colorField.value=colorField.defaultValue;
		SqS('html').style.background=colorField.defaultValue;
		this.classList.add('hidden');
	}
	colorField.onchange=function(){
		reestablecerColor.classList.remove('hidden');
		SqS('html').style.background=this.value;
	}
	
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
	}
	
	gEt('bulk-message').onclick=function(e){
		target=e.target;
		if(target==this)
			this.style.display='none';
		else if(target.id=='bulk-select'){
			createNode('INPUT',{
				type:'file'
				,accept:'.xlsx,.xls,.zip'
				,onchange:function(){
					if(this.files && this.files[0]){
						let chosenAreaOption=+SqS('[name="bulk-area-radio"]:checked').value;
						
						startLoading('img/loading.gif');
						let area={
							isNew:chosenAreaOption
							,data:chosenAreaOption?
								SqS('input.bulk-area-input').value.trim()
								:gEt('bulk-area-select').value
						};
						sendPOST('libs/art/bulk.php',{
							articulos:this.files[0]
							,area
							,destacar:+gEt('bulk-destacado').checked
						})
							.then(res=>res.json())
							.then(result=>{
								stopLoading();
								switch(result[0]){
								case 1:
									let arts=result[1].arts;
									if(!arts.length){
										showMessage('No se han detectado articulos. Revise que el archivo cumpla con el formato necesario.','red');
										return;
									}
									let div=createNode('DIV',{id:'bulk-image-table',children:[
										['TABLE','TBODY']
										,['BUTTON',{
											innerText:'Terminar'
											,id:'bulk-end'
											,dataset:{
												...(area.isNew?{ID:result[1].newAreaID,nombre:area.data}:'')
											}
										}]
										,['BUTTON',{
											innerText:'Cancelar'
											,class:'danger'
											,id:'bulk-cancel'
											,dataset:{
												articulosIDs:arts.map(cur=>cur.ID).join(',')
												,area:area.isNew?result[1].newAreaID:0
											}
										}]
									]})
										,tbody=div.firstChild.firstChild;
									tbody.appendChild(createNode('TR',{
										children:[
											['TH',{innerText:'Artículo'}]
											,['TH',{innerText:'Imagen'}]
										]
									}));
									for(let art of arts)
										tbody.appendChild(createNode('TR',{
											dataset:art
											,children:[
												['TD',{innerText:art.nombre}]
												,['TD',art.ext_de_img?
													['IMG',{
														src:'img/articulo.php?ID='+art.ID
														,class:'bulk-image'
													}]
													:['DIV',{
														dataset:{newid:art.ID}
														,class:'bulk-image-input'
														,children:[
															,['INPUT',{
																type:'file'
																,accept:'image/*'
																,onchange:bulkInputsOnchange
															}]
															,['DIV','SPAN']
														]
													}]
												]
											]
										}));
									
									gEt('bulk-message').children[0].prepend(div);
									break;
								case 0:
									showMessage('Archivo fuera de formato.','red');
									break;
								case -1:
									showMessage('Archivo no soportado.','red');
									break;
								}
							})
							.catch(e=>{
								console.log(e);
								gEt('bulk-message').style.display='none';
								stopLoading();
								alert('Ha ocurrido un error. Reintente más tarde y si el error persiste, comuniquese con el soporte.')
							});
					}else gEt('bulk-message').style.display='none';
				}
			}).click();
		}else if(target.id=='bulk-end'){
			// if(+target.dataset.ID)
			// 	SqS('#art-grid > .articulo').before(crearSec(target.dataset));
			for(let artTR of SqS('tr:not(:first-child)',ALL,gEt('bulk-image-table')))
				crearArticulos(artTR.dataset);
			target.parentNode.remove();
		}else if(target.id=='bulk-cancel'){
			sendJSON('libs/art/bulk-cancel.php',Object.entries(target.dataset).reduce((acc,cur)=>{
				acc[cur[0]]=cur[1];
				return acc;
			},{}));
			target.parentNode.remove();
		}else if(target.tagName=='INPUT' && target.type=='radio'){
			let prev=SqS('.bulk-area-input:not([disabled])',ONLY_ONE,gEt('bulk-area'))
			if(prev)
				prev.disabled=true;
			target.nextElementSibling.disabled=false;
		}
	}
	
	gEt('ped-mas').onclick=()=>fetchPedidos(++pedidosPage);
	
	gEt('ped-exportar').onclick=()=>{
		showOptionsMessage(
			'¿Qué desea exportar?'
			,['Todo el Historial',2]
			,['Los Pedidos Cargados',1]
			,['Cancelar',0]
		)
			.then(res=>{
				let IDs=[];
				switch(+res){
				case 1:
					IDs=[...SqS('#ped-historial > .ped-individual',ALL,gEt('ped-historial'))].map(el=>el.dataset.id);
				case 2:
					download('libs/ped/export.php?IDs='+IDs,'pedidos.xlsx');
					break;
				}
			});
	}
	
	gEt('perfil-contraseña').onclick=function(){
		let newPassword=prompt('Ingrese nueva contraseña:');
		if(newPassword && (newPassword=newPassword.trim())){
			let newPasswordAgain=prompt('Vuelva a ingresar la nueva contraseña:');
			if(newPasswordAgain && newPassword==newPasswordAgain.trim()){
				let oldPassword=prompt('Ingrese su contraseña actual:');
				this.disabled=true;
				sendJSON('libs/user/new-password.php',{newPassword,oldPassword})
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
							showMessage('Se ha actualizado la contraseña',thatColor);
							break;
						}
					})
					.catch(genericCatch)
					.finally(()=>this.disabled=false);
			}else showMessage('Las contraseñas ingresadas no coinciden, intentelo otra vez.');
		}
	}
	
	gEt('busqueda-input').onkeyup=function(e){
		let realValue=this.value.trim();
		startSearch(
			realValue
			,!realValue && e.which==8
			,e.key.length==1 || e.which==8
		);
	};
	
	gEt('busqueda-input').onchange=function(){
		let realValue=this.value.trim();
		startSearch(
			realValue
			,!realValue
		);
	};

	gEt('art-añadir-volver').onclick=volverDelEditor;

	gEt('busqueda-escondidos').onchange=function(){
		let action=this.checked?'add':'remove';
		for(let element of [...SqS('.escondido',ALL)])
			element.classList[action]('escondido-hidden');
	}

	gEt('art-export-panel-export').onclick=function(){
		let selectedArts=[...SqS('.art-exporting-selected',ALL)];
		if(!selectedArts.length)
			return;
		download('libs/art/export.php?articles='+selectedArts.map(e=>e.dataset.id),'articulos.zip');
	}
	
	gEt('art-export-panel-cancel').onclick=function(){
		gEt('art-inicio').classList.remove('art-exporting');
		let div=this.parentNode;
		div.style.display='none';
		div.previousElementSibling.style.display='block';
	}
	
	gEt('art-export-panel-toggle').onclick=function(){
		for(let art of [...SqS('.articulo:not(.escondido-hidden):not(.hidden)',ALL)])
			art.classList.toggle('art-exporting-selected');
	}
	
	gEt('art-export-panel-reset').onclick=function(){
		for(let art of [...SqS('.art-exporting-selected',ALL)])
			art.classList.remove('art-exporting-selected');
	}
	
	gEt('pedidos-pendientes').firstElementChild.onclick=(e)=>{
		let button=e.target
		if(button.tagName!='BUTTON')
			if(button.parentNode.tagName!='BUTTON')
				return;
			else button=button.parentNode;
			
		let confirmado=+button.value
			,theOtherOne=confirmado?button.previousElementSibling:button.nextElementSibling;
		button.disabled=theOtherOne.disabled=true;
		
		sendJSON('libs/evaluar-pedido.php',{confirmado,pedidoID:button.closest('.ped-individual').dataset.id})
			.then(r=>r.text())
			.then(text=>{
				if(+text){
					if(confirmado){
						let parseFecha=tituloSpan=>tituloSpan.innerText.substring(0,10).split('/').reverse().join('/')
							,confirmedPed=button.parentNode.parentNode
							,confirmedFecha=new Date(parseFecha(confirmedPed.firstElementChild))
							,pedHistorial=gEt('ped-historial');
						for(let ped of [...pedHistorial.children,false]){
							if(!ped){
								pedHistorial.appendChild(confirmedPed);
								break;
							}
							let thisFecha=new Date(parseFecha(ped.firstElementChild));
							if(
								thisFecha<confirmedFecha
								|| (
									thisFecha.toString()==confirmedFecha.toString()
									&& +ped.dataset.id < +confirmedPed.dataset.id
								)
							){
								ped.before(confirmedPed)
								break;
							}
						}
						
						button.parentNode.remove();
					}else button.closest('.ped-individual').remove();
				}else button.disabled=theOtherOne.disabled=false;
			});
		}
	
	gEt('pedidos-botones').onclick=function(e){
		let target=e.target;
		if(target==this || this.dataset.showing==target.value)
			return;
		
		for(let x of [target.previousElementSibling,target,target.nextElementSibling])
			if(x)
				x.classList.toggle('pedidos-selected');
		
		gEt('pedidos-pendientes').classList[+target.value?'add':'remove']('pedidos-ova');
		this.dataset.showing=target.value;
	}
	
	gEt('perfil-asciiurl-holder').firstElementChild.onclick=async function(){
		let url=`https://${location.host}/`+this.nextElementSibling.value;
		await navigator.clipboard.writeText(url);
		showMessage(`Se ha copiado "${url}" al portapapeles.`,thatColor);
	}
	
	//fetching

	fetch('libs/get-secciones.php')
		.then(r=>r.json())
		.then(json=>{
			secciones=json;
			
			let objsSecciones=Object.entries(json).map(([ID,nombre])=>[
				'OPTION',{
					value:ID
					,innerText:nombre
				}
			]);
			
			for(let spinner of spinners){
				spinner.innerHTML='';
				addNode(spinner,...objsSecciones);
				spinner.disabled=false;
			}
	
		fetch('libs/get-articles.php')
			.then(r=>r.json())
			.then(arts=>{
				if(arts.length)
					crearArticulos(...arts);
				else{
					gEt('art-grid').prepend(createNode(
						'H2',{
							class:'aviso-art-vacio'
							,innerText:'Todavía no ha subido ningún artículo.'
						}
					));
				}
			})
			.catch(genericCatch);
		
		});
		
	fetchPedidos(0);
		
	
	//other events
	
	perfil.onkeydown=e=>{
		if(e.key=='Escape' && (e.target.nodeName=='INPUT' || e.target.nodeName=='TEXTAREA')){
			let target=e.target;
			target.value=target.defaultValue;
			target.blur();
		}
	}
	
	perfil.onsubmit=function(){
		let changed={}
			,inputs=[
				this.nombre
				,this.numero
				,this.color // index 2 es importante para el color
				,this.descripcion
				,this.minimoCompra
			]
			,image=SqS('[type="file"]',ONLY_ONE,this)
			,backend='libs/update-profile.php'
			
		
		for(let input of inputs){
			let trimmed=input.value.trim();
			if(input.name!='descripcion' && !trimmed){
				alert('No puede dejar ningún campo vacío.');
				resetImagePicker(SqS('.imagePicker').children[2],'img/logo.php');
				changed={};
				break;
			}
			if(trimmed!=input.defaultValue)
				changed[input.name]=trimmed;
			input.disabled=true;
		}
		
		
		if(Object.keys(changed).length !== 0 || (image.files && image.files[0])){
			
			let params=[];
			if(image.files && image.files[0]){
				let fd=new FormData;
				for(let pair of Object.entries(changed))
					fd.append(...pair);
				fd.append('newpfp',image.files[0]);
				params=['ajax',backend,{body:fd}];
			}else params=['sendJSON',backend,changed];
		
			W[params[0]](params[1],params[2])
				.then(r=>r.json())
				.then(json=>{
					for(let [key,value] of Object.entries(json)){
						switch(key){
						case 'newURL':
							gEt('asciiurl').value=value;
							break;
						case 'reloadLogo':
							let newLogoURL='img/logo.php?t='+Date.now();
							resetImagePicker(SqS('.imagePicker').children[2],newLogoURL);
							gEt('logo').src='img/logo.php?t='+newLogoURL;
							break;
						case 'cambiarColor':
							B.parentNode.style.backgroundColor=inputs[2].value;
							break;
						}
					}
					
					for(let input of inputs){
						if(changed[input.name])
							input.defaultValue=input.value;
						input.disabled=false;
					}
					
					
					
					for(let id of[
						'descripcion'
						,'nombre'
						
					])
						if(changed[id])
							gEt(id).innerText=changed[id];
					
					showMessage('Los datos han sido actualizados.',thatColor);
				});
		
		}else for(let input of inputs)
			input.disabled=false;
		return false;
	}
	
	D.forms['art-añadir'].onsubmit=function(){
		let campos={
			nombre:this.Nombre
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
