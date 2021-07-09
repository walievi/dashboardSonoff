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
	$wh = " WHERE 1 = 1 ";
	if(count($where) > 0){
		foreach ($where as $col => $value) {
			$wh .= "AND ". $col ." = '". $value ."'";
		}
		return $wh;	
	}
	return "";
}

function getLeituras($limit = 5){
	$con = connect();
	$rs = $con->prepare("SELECT * FROM leituras ORDER BY momento ASC LIMIT ". $limit);


	if($rs->execute()){
		if($rs->rowCount() < 1)
			return;
		
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
    throw new Exception("Deu ruim", true);
}


function saveDadosConsumo($dados, $leituras){
	$pdo = connect();
	$pdo->beginTransaction();

	try {
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("INSERT INTO dados_leitura(equipamento_id, potencia, tensao, corrente, momento) VALUES(:equipamento_id, :potencia, :tensao, :corrente, :momento)");

		foreach ($dados as $dado) {
			$fields = array();
			foreach ($dado as $key => $value)
				$fields[':'.$key] = $value;

			$stmt->execute($fields);
		}


		$stmt = $pdo->prepare('DELETE FROM leituras WHERE id = :id');
 		
 		foreach ($leituras as $leituraId) 
  			$stmt->execute(array(':id' => $leituraId));

  
	} catch(\Throwable $e) { // use \Exception in PHP < 7.0
	    $pdo->rollBack();
	    throw $e;
	}

	$pdo->commit();
}

function getConfig($chave){

	$con = connect();
	$rs = $con->prepare("SELECT valor FROM configs WHERE chave = :chave ");


	if(!$rs->execute(array(":chave" => $chave)))
		throw new Exception("Erro ao executar a consulta", true);
		

	if($rs->rowCount() != 1)
		throw new Exception("Erro na Chave", true);
	

	while($row = $rs->fetch(PDO::FETCH_OBJ)){
		return $row->valor;
	}
	
}


function getTable($table, $select = "*", $where = array()){
	$select = prepareSelect($select);

	$where = prepareWhere($where);

	$con = connect();
	$rs = $con->prepare("SELECT ". $select ." FROM ". $table . $where);

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


function insertPing($obj){
	$pdo = connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$stmt = $pdo->prepare("INSERT INTO tmp_status_pcs(pc_id, ping_result, ligado, momento) VALUES(:pc_id, :ping_result, :ligado , now())");
	
	$fields = array();
	foreach ($obj as $key => $value)
		$fields[':'.$key] = $value;

	$stmt->execute($fields);

	return $pdo->lastInsertId();
}

function registraEvento($equipId, $acao, $origem){
	$pdo = connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$stmt = $pdo->prepare("INSERT INTO equipamento_evento(equipamento_id, tipo, origem, momento) VALUES(:equip_id, :tipo, :origem, now())");

	$obj = new stdClass();
	$obj->equip_id = $equipId;
	$obj->tipo = $acao;
	$obj->origem = $origem;

	$fields = array();
	foreach ($obj as $key => $value)
		$fields[':'.$key] = $value;

	$stmt->execute($fields);

	return $pdo->lastInsertId();
}


function getLastStatus($equipId){

	$con = connect();
	$rs = $con->prepare("
			SELECT *, 
				IF (now() - INTERVAL (SELECT valor FROM configs WHERE chave = 'tempo_offlinepc') SECOND > momento, true , false) AS venceu	 
			FROM equipamento_evento ev
			WHERE equipamento_id = :equipId
			ORDER BY momento DESC 
			LIMIT 1
		");

	if(!$rs->execute(array(":equipId" => $equipId)))
		throw new Exception("Erro ao executar a consulta", true);

	while($row = $rs->fetch(PDO::FETCH_OBJ)){
		return $row;
	}

	return;
}


function temPcsLigados($equipId){
	$con = connect();
	$rs = $con->prepare("SELECT temPcLigado(:equipId) AS ligado");


	if(!$rs->execute(array(":equipId" => $equipId)))
		throw new Exception("Erro ao executar a consulta", true);
		

	if($rs->rowCount() != 1)
		throw new Exception("Erro na Chave", true);
	

	while($row = $rs->fetch(PDO::FETCH_OBJ)){
		if($row->ligado)
			return true;

		return false;
	}
}


function getDadosConsumo($equipId, $periodo, $fim = "now()"){
	switch ($periodo) {
		case 'dia':
			$query = "SELECT kWh, formatada FROM vw_dados_consumo_dia WHERE ts > (". $fim ." - INTERVAL 30 DAY)";
			break;
		
		case 'mes':
			$query = "SELECT kWh, formatada FROM vw_dados_consumo_mes WHERE ts > (". $fim ." - INTERVAL 12 MONTH)";
			break;

		case 'ano':
			$query = "SELECT kWh, formatada FROM vw_dados_consumo_ano";
			break;

		case 'hora':
			$query = "SELECT kWh, SUBSTR(formatada, 1, 3) AS formatada FROM vw_dados_consumo_hora WHERE data > (". $fim ." - INTERVAL 24 HOUR)";
			break;

		case 'sem':
			$query = "SELECT kWh, formatada FROM vw_dados_consumo_dia WHERE ts > (". $fim ." - INTERVAL 7 DAY)";
			break;

		default:
			throw new Exception("Periodo Inválido", true);			
	}
	$con = connect();

	$rs = $con->prepare($query);

	if(!$rs->execute())
		throw new Exception("Erro na Consulta", true);

	if($rs->rowCount() == 0)
		return array();

	$registros = array();
	while($row = $rs->fetch(PDO::FETCH_OBJ)){
		$registros[$row->formatada] = $row->kWh;
	}

	return $registros;

}


function getEquipamentosPorLab($labId){
	$labId = 1;
	$equipamentos = getTable("equipamentos", array('id', 'nome', 'ip', 'temPcLigado(id) as temPcLigado'), array("ativo" => 1, "laboratorio_id" => $labId));

	$nomesCol = array(
		"nome" => "Nome do Equipamento",
		"ip" => "IP",
		"temPcLigado" => "Status dos PCs"
	);

	$lista = alteraNomesParaExib($nomesCol, $equipamentos);
	foreach ($lista as $key => $value) {
		$lista[$key]->{"Potência Atual"} = getPotenciaMedia($value->id) ." Watts";
	}

	return $lista;
}

function alteraNomesParaExib($nomesCol, $registros){
	$return = array();
	foreach ($registros as $reg) {

		$obj = new stdClass();

		foreach ($reg as $key => $value) {
			if(isset($nomesCol[$key]))
				$obj->{$nomesCol[$key]} = $value;
			else
				$obj->{$key} = $value;
		}

		$return[] = $obj;
	}

	return $return;
}



function getPotenciaMedia($equipId){
	$query = "
		SELECT FORMAT(AVG(potencia),2) AS potenciaMedia
		FROM dados_leitura
		WHERE equipamento_id = :equipId 
			AND momento > NOW() - INTERVAL 5 MINUTE
		    GROUP BY equipamento_id
	";

	$con = connect();

	$rs = $con->prepare($query);

	if(!$rs->execute(array(":equipId" => $equipId)))
		throw new Exception("Erro na Consulta", true);

	if($rs->rowCount() == 0)
		return array();

	$registros = array();
	while($row = $rs->fetch(PDO::FETCH_OBJ)){
		return $row->potenciaMedia;
	}

	return $registros;

}
