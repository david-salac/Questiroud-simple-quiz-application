<?php
header('Access-Control-Allow-Origin: *', false);

$jsonobj = file_get_contents("questions.json");
$decoded = json_decode($jsonobj);

//echo in_array("option_0_1", $_POST);

var_dump($_POST);


//echo $decoded["questionnaire"];

$question = 0;
$total_score = 0;
foreach ($decoded as $key => $value) {
    foreach ($value as $key => $value) {
        $option = 0;
        $score = 0;
        foreach ($value as $key => $value) {
            //echo $key;
            //var_dump($value);
            if (in_array("option_" . (string)$question . "_" . (string)$option, $_POST)) {
                ///echo $key;
                var_dump($value);
            }
            $option++;
        }
        $question++;
    }

    //echo $value;
}

//var_dump(json_decode($jsonobj));

//var_dump($_POST);

?>