<?php
require("../vendor/autoload.php");

$openapi = \OpenApi\Generator::scan([$_SERVER["DOCUMENT_ROOT"]."/api/models"]);

header('Content-Type: application/json');
echo $openapi->toJson();