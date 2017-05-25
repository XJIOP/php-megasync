<?php
require_once("megasync.class.php");

/*************************************************
 *
 * php megasync.php "/local/files" "/mega/files"
 *
 *************************************************/

$megatools = new megaSync();

$megatools->CONFIG["LOG"] = true;
$megatools->CONFIG["LOGIN"] = ""; 
$megatools->CONFIG["PASS"] = ""; 
$megatools->CONFIG["ID"] = "--username ".$megatools->CONFIG["LOGIN"]." --password ".$megatools->CONFIG["PASS"];
$megatools->CONFIG["EXTENSION"] = array();

$megatools->sync();

?>
