<?php
header('Access-Control-Allow-Origin: *', false);
echo file_get_contents("questions.json");
?>