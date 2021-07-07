<?php

	require_once "functions.php";
	require_once "database.php";



	$tasmotas = getTable("equipamentos", "*", array("ativo" => true));

	foreach ($tasmotas as $tasmota) {
		$log = new stdClass();
		$log->Equipamento_ID = $tasmota->id;
		$log->Equipamento_Nome = $tasmota->nome;
		$log->Equipamento_IP = $tasmota->ip;

		try {
			$retorno = tasmotaFaz($tasmota->ip, "sensores");
			if(is_null($retorno))
				throw new Exception("Erro ao consultar o sensor", true);
				
			$log->retorno = $retorno;

			$leitura = new stdClass();
			$leitura->equipamento_id = $tasmota->id;
			$leitura->retorno = $retorno->StatusSNS->ENERGY;


			salvaLeitura($leitura);


		} catch (Exception $e) {
			$log->Status = "Erro";
			$log->erro = $e->getMessage();
		}

		registraLog("ConsultaEquipamento", $log);
	}








