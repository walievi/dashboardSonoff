<?php 


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

	require_once "database.php";
	require_once "functions.php";

	function desligAutomatico(){
		foreach (getTable('equipamentos', "*", array("ativo" => true)) as $equip) {

			if(temPcsLigados($equip->id))
				continue;

			if(!getLastStatus($equip->id)->venceu)
				continue;

			$info = getTasmota($equip->id, "getPowerStatus");

			if($info->POWER != "ON")
				continue;


			setTasmota($equip->id, "powerOff", "Automático");	
			
		}
	}


	function operacaoManual(){
		foreach (getTable('equipamentos', "*", array("ativo" => true)) as $equip) {
			$info = getTasmota($equip->id, "getPowerStatus");

			$acoes = array(
				"ON" => "PowerOn",
				"OFF" => "PowerOff"
			);

			if($acoes[$info->POWER] != getLastStatus($equip->id)->tipo)
				registraEvento($equip->id, $acoes[$info->POWER], "Operação Manual");

		}
	}














operacaoManual();
desligAutomatico();