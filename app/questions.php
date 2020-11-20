<?php
header('Access-Control-Allow-Origin: *', false);

header('Content-type: application/json; charset=utf-8');
echo file_get_contents("questions.json");
?>