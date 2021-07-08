<?php

	require_once "functions.php";
	require_once "database.php";


	DEFINE ('LIMIT_ERROS_ACEITOS', 100);
	$listaDados = array();
	$idLeituras = array();
	$logs = array();



	$leituras = getLeituras(50);
	$errosLeituras = 0;


	foreach ($leituras as $leitura) {
		try {
			$obj = new stdClass();

			$obj->equipamento_id = $leitura->equipamento_id;
			$obj->momento = $leitura->momento;

			$retorno = json_decode($leitura->retorno);

			$obj->tensao = $retorno->Voltage;
			$obj->potencia = $retorno->Power;
			@$obj->corrente = (float) (((int) $retorno->Power) / ((int) $retorno->Voltage)); 

			$idLeituras[] = $leitura->id;

			foreach ($obj as $key => $value){
				if(empty($value)){			
					$log = new stdClass();
					$log->Equipamento_ID = $leitura->equipamento_id;
					$log->mensagem = "Erro na Leitura";
					$log->valores = $obj;
					$logs[] = $log;

					throw new Exception("Erro na Leitura", true);
				}
			}

			$listaDados[] = $obj;

		} catch (Exception $e) {
			++$errosLeituras;

			if($errosLeituras > ((LIMIT_ERROS_ACEITOS / 100) * count($leituras))){
				$log = new stdClass();
				$log->mensagem = "Limite de leituras falhas extrapolado";
				$log->dados = $leituras;

				registraLog("ExessoErrosProcLeitura", $log);

				die("Limite de leituras falhas extrapolado");			
			}
		}
	}

	saveDadosConsumo($listaDados, $idLeituras);

	foreach ($logs as $log) {
		registraLog("ErroLeitura", $log);
	}
