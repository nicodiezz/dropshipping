<?php

function toAsciiURL($string){
	// return str_replace(' ','_',iconv('UTF-8', 'ASCII//TRANSLIT',strstr($string,['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'o','Ú'=>'U','ñ'=>'n','Ñ'=>'N'])));
	setlocale(LC_CTYPE, 'es_AR');
	return
		// strtolower(
			str_replace(' ','_',
				preg_replace('/[^a-z- 0-9]/i','',
					iconv('UTF-8', 'ASCII//TRANSLIT',$string)
				)
			// )
		);
}

?>