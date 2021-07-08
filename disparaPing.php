<?php

	require_once "database.php";

	$pcs = getTable("pcs");

	$url = "http://192.168.100.100/pingTest.php";
	foreach ($pcs as $pc) {
		exec ("curl '". $url ."?ip=". $pc->hostname ."&dev=". $pc->id ."'");		
	}
