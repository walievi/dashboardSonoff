<?php

	require_once "database.php";
	require_once "functions.php";

	setTasmota($_GET['equipId'], "powerOn", "Interface Web");	

	echo "<script>window.frames.closewindow();</script>";