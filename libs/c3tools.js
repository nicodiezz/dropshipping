 	//Utilities

const W=window,D=document,ALL=true,ONLY_ONE=false,OTHER=-1,NUMBER=0,STRING=1,ARRAY=2,OBJECT=3,BOOLEAN=4,NULL=5;
var B;


addEventListener('DOMContentLoaded',()=>{
	B=D.body;
});


function is(variable,type){
	if(variable==null && type==NULL)
		return true;
	let types={
		0:['number',Number]
		,1:['string',String]
		,4:['boolean',Boolean]
	}
	switch(type){
		case 0:
		case 1:
		case 4:
			return types[type][0]==typeof variable || variable instanceof types[type][1];
	// case 0:
	// 	return 'number'==typeof variable || variable instanceof Number;
	// case 1:
	// 	return 'string'==typeof variable || variable instanceof String;
		case 2:
			return Array.isArray(variable);
		case 3:
			return 'object'==typeof variable && !Array.isArray(variable);
	// case 4:
	// 	return 'boolean'==typeof variable || variable instanceof Boolean;
	}
}

function whatIs(variable){
	switch(typeof variable){
	case 'number':
		return NUMBER;
	case 'string':
		return STRING;
	case 'object':
		switch(true){
		case Array.isArray(variable):
			return ARRAY;
		case variable instanceof String:
			return STRING;
		case variable instanceof Number:
			return NUMBER;
		case variable==null:
			return NULL;
		default:
			return OBJECT;
		}
	default:
		return OTHER;
	}
}

const gEt=(id,document=D)=>document.getElementById(id);

function SqS(selector,cantidad=ONLY_ONE,ancestroComun=D){
	if(selector instanceof Node)//??? Node vs Element
		return selector;
	if(is(selector,STRING)){
		let resultados
			,selectorInfo={
				restoDeSelector:selector.slice(1)
				,esComplejo:false
			};
		for(let complexChar of [' ',':','[','.','#',','])
			if(selectorInfo.restoDeSelector.includes(complexChar)){
				selectorInfo.esComplejo=true;
				break;
			}
		if(selectorInfo.esComplejo)
			if(!cantidad||cantidad===1)
				return ancestroComun.querySelector(selector)
			else if(cantidad===true)
				return ancestroComun.querySelectorAll(selector);
			else resultados=ancestroComun.querySelectorAll(selector);
		else switch(selector[0]){
		case '#':
			return D.getElementById(selectorInfo.restoDeSelector);
		case '.':
			resultados=ancestroComun.getElementsByClassName(selectorInfo.restoDeSelector);
			break;
		case '[':
			let nameMatch=/^\[name="([^"]*)"\]$/.exec(selector);
			if(nameMatch)
				resultados=D.getElementsByName(nameMatch[1]);
			break;
		case ':':
			break;
		default:
			resultados=ancestroComun.getElementsByTagName(selector);
		}
		if(!cantidad||cantidad===1)
			return resultados?resultados[0]:D.querySelector(selector);
		else if(cantidad===true)
			return resultados?resultados:D.querySelectorAll(selector);
		else{
			if(!resultados)
				resultados=D.querySelectorAll(selector);
			if(cantidad>=resultados.length)
				return resultados;
			let respuesta=[];
			for(let i=0;i<cantidad;i++)
				respuesta.push(resultados[i]);
			return respuesta;
		}
	}else return false;
}

function last(array){
	if(!is(array,ARRAY))
		throw new Error('Tried to get last of something not an array.');
	return array[array.length-1];
}

//Nodes

function createNode(element,options,onlyChild){
	// let {props,children,finalFun,}=options;
	if(!element)
		return;
	
	let finalFun;
	
	if(is(element,ARRAY))
		[element,options,onlyChild]=element;
	if(is(element,STRING))
		if(element=element.trim())
			element=D.createElement(element.toUpperCase());
		else return;
	
	if(options && (options.nodeType || !is(options,OBJECT))){
		onlyChild=options;
		options=null;
	}
	
	let value;
	if(options)
		for(let key in options){
			value=options[key];
			
			switch(key){
			case 'class':
				element.classList.add(value);
				break;
			case 'classList':
				for(let item of value)
					element.classList.add(item);
				break;
			case 'finalFun':
				finalFun=value;
				break;
			case 'children':
				addNode(element,...value);
				break;
			default:
				if(key.substring(0,2)=='on' && is(value,STRING))
					if(value.match('[^a-zA-Z0-9_]'))
						element[key]=new Function(value);
					else element[key]=W[value];
				else if(key.substring(0,2)!='on' && element[key]==undefined)//this is null too right?  probar algun dia, hacer test set 	//TODO do please
					element.setAttribute(key,value);
				else if(is(value,OBJECT)) //style, dataset
					Object.assign(element[key],value);
				else element[key]=value;
				break;
			}
			// if(key=='innerHTML')
			// 	processJSinHTML(value);
		}
	if(onlyChild)
		element.appendChild(onlyChild.nodeType?onlyChild:createNode(onlyChild));
	if(finalFun)
		(typeof finalFun=='string'?new Function(finalFun):finalFun).call(element);
	return element;
}

function addNode(parent,...children){
	let results=[];
	for(let child of children)
		if(child)
			results.push(parent.appendChild(child.nodeType?child:createNode(child)));
	return results.length>1?results:results[0];
}

//fetching

function sendJSON(url,JSONdata,otherOptions=null){
	let defaultOptions={
		credentials:'include'
		,method:'POST'
		,headers:{'Content-Type':'application/json'}
		,body:JSON.stringify(JSONdata)
	};
	return fetch(url,otherOptions?Object.assign(defaultOptions,otherOptions):defaultOptions);
}

function JSONAsFormData(obj){
	if(!obj)
		return;
	
	let fd=new FormData();
	for(let key in obj)
		fd.append(key,obj['key']);
	return fd;
}

function* JSONAsURLEncodedStringIterator(obj,prefix=null){
	let pairs=Array.isArray(obj)?
		obj.map(el=>['',el])
		:Object.entries(obj);
	for (let [k,v] of pairs){
		k = prefix ? prefix + "[" + k + "]" : k;
		if(v != null && typeof v == "object")
			yield* JSONAsURLEncodedStringIterator(v, k);
		else yield [k,v];
	}
}

//TODO test returnType - DON'T USE IT
function sendPOST(url,data,returnType=null,otherOptions=null){
	if(!(data instanceof FormData)){
		let tempfd=new FormData;
		for(let key in data){
			let value=data[key];
			if(value != null && !(value instanceof File) && typeof value == "object"){
				for(let pair of JSONAsURLEncodedStringIterator(value,key))
					tempfd.append(...pair);
			}else tempfd.append(key,value);
		}
		data=tempfd;
	}
	
	let options={
		credentials:'include'
		,method:'POST'
		,body:data
	};
	if(otherOptions)
		Object.assign(options,otherOptions);
	
	return returnType?
		fetch(url,options).then(r=>r[returnType]())
		:fetch(url,options);
}

function fetchConCredentials(URL,options,...rest){
	return fetch(URL,Object.assign({credentials:'include'},options),...rest);
}

//custom nodes

const COVER_STYLE={
	position:'fixed'
	,top: 0,left: 0
	,display:'flex'
	,height: '100%',width: '100%'
	,background:'rgba(0,0,0,.5)'
	,zIndex:10000
}
;

//loading

/* TODO
	be able to append it to some div inside, consider variable size containers
	principalmente para el tema de imagen rotando vs gif
*/

class Loader{
	
	static DEFAULT_ALPHA=.5;
	static DEFAULT_ROTATION_SPEED=1.5;
	static DEFAULT_SPIN_CLASS='loader-spin';
	
	constructor(
		{
			image:img
			,alpha
			,rotationSpeed
			,spinClass
		}={
			alpha:Loader.DEFAULT_ALPHA
			,rotationSpeed:Loader.DEFAULT_ROTATION_SPEED
		}
	){
		if(is(arguments[0],STRING))
			img=arguments[0];
		this.itself=createNode('DIV',{style:COVER_STYLE},[
			'IMG'
			,{
				src:img
				,style:{
					margin:'auto'
					,maxHeight:'20vmin'
				}
			}
		]);
		this.image=this.itself.firstElementChild;
	
		this.spinClass=spinClass||Loader.DEFAULT_SPIN_CLASS;
		if(!this.image.src.endsWith('.gif'))
			this.image.classList=this.spinClass;
		if(alpha!=.5)
			this.setAlpha(alpha);
		if(+rotationSpeed)
			this.setRotationSpeed(rotationSpeed);
	}
	start(where=B){
		where.appendChild(this.itself);
	}
	stop(where=B){
		where.removeChild(this.itself);
	}
	setAlpha(newAlpha){
		this.itself.style.background=`rgba(0,0,0,${newAlpha})`;
	}
	setImage(newImgURL){
		this.image.src=newImgURL;
	}
	setRotationSpeed(newRotationSpeed=Loader.DEFAULT_ROTATION_SPEED){
		if(this.image.classList.contains(this.spinClass))
			this.image.style.durationSpeed=newRotationSpeed+'s';
	}
	setOptions({alpha,rotationSpeed}={alpha:Loader.DEFAULT_ALPHA,rotationSpeed:Loader.DEFAULT_ROTATION_SPEED}){
		this.setAlpha(alpha);
		this.setRotationSpeed(rotationSpeed);
	}
	forceSpinClass(){
		if(!this.image.classList.contains(this.spinClass))
			this.image.classList.add(this.spinClass);
	}
	setSpinClass(newSpinClassName){
		if(this.image.classList.contains(this.spinClass))
			this.image.classList.replace(this.spinClass,newSpinClassName);
		this.spinClass=newSpinClassName;
	}
}

const LOADING=createNode('DIV',{style:COVER_STYLE},[
	'IMG'
	,{style:{
		margin:'auto'
		,maxHeight:'20vmin'
	}}
]);
var defaultLoadingImageURL;

function startLoading(imageURL=defaultLoadingImageURL,alpha=.5){
	let originalSRC=LOADING.firstChild.src;
	if(!(originalSRC || imageURL || defaultLoadingImageURL))
		return;
	if(!defaultLoadingImageURL)
		defaultLoadingImageURL=imageURL;
	if(originalSRC!=imageURL)//probably unnecessary
		LOADING.firstChild.src=imageURL;
	let newBackground='rgba(0,0,0,'+alpha+')';
	if(LOADING.style.background!=newBackground)//check, and if browser independent
		LOADING.style.background=newBackground;
	B.appendChild(LOADING);
}

function stopLoading(){
	B.removeChild(LOADING);
}

//custom alerts (probably custom prompts in the future)

const ALERT_MESSAGE=createNode('DIV',{
	style:COVER_STYLE
	,onclick:function(e){
		if(e.target==this)
			dismissMessage();
	}
},[
	'DIV'
	,{
		style:{
			background:'white'
			,borderRadius:'10px'
			,margin:'auto'
			,maxWidth:'90%'
			,padding:'15px'
			,boxSizing:'border-box'
			,border:'solid 4px'
		}
	}
	,'SPAN'
]);
var defaultMessageBorderColor='#AAA';

function showMessage(message,color=defaultMessageBorderColor,alpha=.5){
	if(!(message=message.trim()))
		return;
	let child=ALERT_MESSAGE.firstChild;
	child.firstChild.innerText=message;
	
	const childStyle=child.style
		,newBorderColor=color
		,divStyle=ALERT_MESSAGE.style
		,newBackgroundColor='rgba(0, 0, 0, '+alpha+')'
		;
		
	if(childStyle.borderColor!=newBorderColor)
		childStyle.borderColor=newBorderColor;
	if(divStyle.backgroundColor!=newBackgroundColor)
		divStyle.backgroundColor=newBackgroundColor;
	
	B.appendChild(ALERT_MESSAGE);
}

function dismissMessage(){
	B.removeChild(ALERT_MESSAGE);
}

var optionsMessageReject;
const OPTIONS_MESSAGE=createNode('DIV',{
	style:COVER_STYLE
	,onclick:function(e){
		if(e.target==this){
			
			B.removeChild(OPTIONS_MESSAGE);
			// dismissMessage();
			
			if(optionsMessageReject)
				optionsMessageReject();
		}
	}
},[
	'DIV'
	,{
		style:{
			background:'white'
			,borderRadius:'10px'
			,margin:'auto'
			,maxWidth:'90%'
			,padding:'15px'
			,boxSizing:'border-box'
			,border:'solid 4px'
		}
		,children:[
			[
				'P'
				,{
					style:{
						textAlign:'center'
					}
				}
			]
			,[
				'DIV'
				,{
					style:{
						display:'flex'
						,justifyContent:'space-evenly'
						,gap: '10px'
					}
				}
			]
		]
	}
]);

function showOptionsMessage(message,...options){
	
	let container=OPTIONS_MESSAGE.firstChild
		,editables=container.children;
	
	editables[0].innerText=message;
	
	let buttonsDIV=editables[1]
	buttonsDIV.innerHTML='';
	for(let option of options){
		buttonsDIV.appendChild(createNode(
			'BUTTON'
			,{
				innerText:option[0]
				,value:option[1]
			}
		));
	}
	
	B.appendChild(OPTIONS_MESSAGE);
	
	return new Promise((resolve,reject)=>{
		
		optionsMessageReject=reject;
		
		buttonsDIV.addEventListener('click',e=>{
			let target=e.target;
			if(target.nodeName=='BUTTON' && OPTIONS_MESSAGE.parentNode){
				B.removeChild(OPTIONS_MESSAGE);
				resolve(target.value);
			}
		},{once:true});
	
	});
	
}

// legacy

function ajax(url,options=null/*,returnType*/){
	let defaultOptions={
		credentials:'include'
		,method:'POST'
	};
	
	let opciones=options?
		Object.assign(defaultOptions,options)
		:defaultOptions;
	
	// let fench=fetch(url,options?Object.assign(defaultOptions,options):defaultOptions);
	// return returnType?fench[returnType]:fench;
	return (
		sendPOST(url,opciones.body)
		// ;
	// fench;
	);
}