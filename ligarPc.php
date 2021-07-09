<?php

	require_once "database.php";
	require_once "functions.php";

	setTasmota($_GET['equipId'], "powerOn", "Automático");	

	echo "<script>window.frames.closewindow();</script>";