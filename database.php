<?php 
DEFINE ("SERVER", "192.168.100.100");
DEFINE ("DATABASE", "dashboard");
DEFINE ("DBUSER", "dashboard");
DEFINE ("DBPASSWD", "dashboard");



function connect(){
	return new PDO("mysql:host=". SERVER .";dbname=". DATABASE, DBUSER, DBPASSWD);
}


function prepareSelect($select){
	if(is_array($select)){
		foreach ($select as $col) {
			$fields .= $col .", ";
		}
		return substr($fields, 0, -2);
	}
	return $select;
}

function prepareWhere($where){

	if(count($where) > 0){
		foreach ($where as $col => $value) {
			$wh .= $col ." = '". $value ."' AND ";
		}
		return substr($wh, 0, -5);	
	}

	return "";
}


function getTable($table, $select = "*", $where = array()){
	$select = prepareSelect($select);

	$where = prepareWhere($where);

	$con = connect();
	$rs = $con->prepare("SELECT ". $select ." FROM ". $table ." WHERE ". ((empty($where)) ? "1 = 1" : $where) );


	if($rs->execute()){
		if($rs->rowCount() > 0){

			$registros = array();
			while($row = $rs->fetch(PDO::FETCH_OBJ)){
				$obj = new stdClass();

				foreach ($row as $colName => $colValue) {
					$obj->{$colName} = $colValue;
				}
				$registros[] = $obj;
			}

			return $registros;
	    }
	}
    throw new Exception("Deu ruim", true);
    
}


function registraLog($tipo, $informacao){
	$pdo = connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$stmt = $pdo->prepare("INSERT INTO logs(tipo, log, momento) VALUES(:tipo, :log, now())");
	$stmt->execute(array(
    	':tipo' => $tipo,
    	':log' 	=> json_encode($informacao)
	));

	return $pdo->lastInsertId();
}


function salvaLeitura($leitura){
	$pdo = connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$stmt = $pdo->prepare("INSERT INTO leituras(equipamento_id, retorno, momento) VALUES(:equiId, :retorno, now())");
	$stmt->execute(array(
    	':equiId' 	=> $leitura->equipamento_id,
    	':retorno' 	=> json_encode($leitura->retorno)
	));

	return $pdo->lastInsertId();
}