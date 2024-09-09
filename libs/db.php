<?php
	class dbcon{
		const NUM=-1;
		const BOTH=0;
		const ASSOC=1;
		var $dblink;
		var $error;
		
		function __construct(){
			// $this->dblink = new mysqli('localhost','c2410926_hlp','vi72FOnaru','c2410926_hlp');//$dbhost, $dbuser, $dbpass, $dbname);
			$this->dblink = new mysqli('localhost:3306','root','password','redconstruccion');//$dbhost, $dbuser, $dbpass, $dbname);
			$this->dblink->set_charset("utf8");
			register_shutdown_function(array(&$this, 'destruct'));
		}
		function destruct(){
			$this->dblink->close();
		}
		function query($q){
			$result=$this->dblink->query($q);
			if($this->dblink->error){
				throw new Exception("MySQL error {$this->dblink->error}
Query: \"$q\"
",$this->dblink->errno);
			}
			return $result;
		}
		function result($consulta,$fila=0,$columna=0){
			if(is_string($consulta)){
				if(!($consulta=trim($consulta)))
					return false;
				$consulta=$this->query($consulta);
			}
			$consulta->data_seek($fila);
			$result=$consulta->fetch_array();
			return $result[$columna];
		}
		function insert_id(){
			return $this->dblink->insert_id;
		}
		function affected_rows(){
			return $this->dblink->affected_rows;
		}
		function prepared($q,$t,$a,$o=0){ //o...?  options?? TODO investigar prepared statements parameters
			if($s=$this->dblink->prepare($q)){
				$error=false;
				if(is_array($a)?$s->bind_param($t,...$a):$s->bind_param($t,$a))
					if($s->execute())
						switch(strtoupper(substr($q,0,3))){
							case 'INS':
							case 'UPD':
							case 'DEL':
								$a=$s->affected_rows;
								$s->close();
								return $a;
								break;
							case 'SEL':
								$a=$s->get_result();
								$s->close();
								return $a?:false;
								break;
							default:
								$this->error='Not prepared statement-supported query type.';
								break;
						}
					else $error=true;
				else $error=true;
				if($error)
					$this->error=$s->error;
				return false;
			}else $this->error=$this->dblink->error;
			return false;
		}
	}
$db=new dbcon();
?>