<?php

$openapi = json_decode(file_get_contents(__DIR__ . "/openapi.json"));


echo json_encode($openapi, JSON_PRETTY_PRINT);