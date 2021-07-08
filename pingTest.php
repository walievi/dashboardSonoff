<?php

	require_once "database.php";

	$host = $_GET["ip"];
	$pc_id = $_GET["dev"];

	if(empty($host) || empty($pc_id))
		throw new Exception("Dados Inválidos", true);
		
//	Erro DEV no MacOS
//	$result = "3 packets transmitted, 0 received, +3 errors, 99% packet loss, time 2047ms";
//	$output = "temporario";
	
	exec("ping -w 4 " . $host, $output, $result);
	$pos = count($output) - 2;

	$result = $output[$pos];


	$lostPercent = trim(substr($result, (strpos($result, "%") -2), 2));

	$obj = new stdClass();
	$obj->pc_id = $pc_id;
	$obj->ping_result = json_encode($output);
	$obj->ligado = (($lostPercent !== "00") ? 1 : 0);


	insertPing($obj);