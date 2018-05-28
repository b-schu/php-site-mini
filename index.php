<?php

include("shell/mini.php");

$m = new mini();
$m->start();
$m->install();

$LOGGED_IN = $m->logged_in();
$USER = $m->get_user_by_name($_SESSION["username"]);

$app = $_REQUEST["app"];
if ($app == "") {
	$app = "interface";
}

include("applications/$app/index.php");


?>
