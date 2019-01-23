<?php
// This script shows the eq3 info in a simple output format.

// example to call this page
// http://127.0.0.1/eq3/eq3read.php

echo "<pre>";

require_once("appsettings.php");
require_once("eq3class.php");
$eq3 = new Eq3Class;

$eq3arr = $eq3->OpenSock($cubehost,$cubeport);
print_r($eq3->retarr);
$eq3arr = $eq3->GetValues("all");
$eq3->CloseSock();

print_r($eq3arr);

echo "</pre>";
?>