<?php
header('Access-Control-Allow-Origin: *', false);
header('Content-type: application/json; charset=utf-8', false);

$LINK_TO_JSON = "questions.json";
$SUCCESS_MESSAGE = "Email sent!";

/*
TODO:
1. Styles
1.a. Passing name/email as questions wrapper arg.
2. Storing to file
3. Body wrapper
4. Sending to email.
*/

// =================== RENDER FRACTION OF CODE FOR RESPONSE ===================
function render_html_question($question_text, $order) {
    /**Render question
    @param question_text: Text (string) of the question.
    @param order: Relative position (indexed from 1).
    @return: HTML string defining the question.
    */
    return "<h2>".$order.") ".$question_text."</h2>";
}
function render_html_option_correct($option_text, $order) {
    /**Correct answer selected by user
    @param option_text: Text (string) of the option.
    @param order: Relative position (indexed from 1).
    @return: HTML string defining the option.
    */
    return "<strong style='background: #31AD00;'>".$option_text."</strong><br>";
}
function render_html_option_incorrect($option_text, $order) {
    /**Incorrect answer selected by user
    @param option_text: Text (string) of the option.
    @param order: Relative position (indexed from 1).
    @return: HTML string defining the option.
    */
    return "<strong style='background: red;'>".$option_text."</strong><br>";
}
function render_html_option_unselected_correct($option_text, $order) {
    /**Correct answer NOT selected by user
    @param option_text: Text (string) of the option.
    @param order: Relative position (indexed from 1).
    @return: HTML string defining the option.
    */
    return "<strong style='background: #B7EBA2;'>".$option_text."</strong><br>";
}
function render_html_option_others($option_text, $order) {
    /**Incorrect unselected answer
    @param option_text: Text (string) of the option.
    @param order: Relative position (indexed from 1).
    @return: HTML string defining the option.
    */
    return "<strong>".$option_text."</strong><br>";
}
function render_question_options_wrapper($question_plus_options, $score) {
    /**Wrapper for question plus option*/
    return '<div style="background: #ededed; margin-bottom: 20px">'.$question_plus_options."<h3>Score: ".number_format($score, 2)."</h3></div>";
}
function render_all_questions_wrapper($all_questions, $total_score, $total_question_number) {
    /**Wrapper for all question plus options*/
    return '<div style="background: #efefef; padding: 20px">'.$all_questions."<h3>Total score: ".number_format(100 * $total_score/$total_question_number, 2)." percent</h3></div>";
}
// ============================================================================

function get_list_of_post_keys($decoded_json) {
    /**Return list of all acceptable values in JSON for POST keys.

    @param decoded_json: JSON dataclass with all questions and answers.
    */
    $list_of_options = array();
    $question_order = 0;
    foreach ($decoded_json->questionnaire as $question) {
        $option_order = 0;
        foreach ($question->options as $option) {
            array_push($list_of_options, "option_" . $question_order . "_" . $option_order);
            array_push($list_of_options, "option_" . $question_order);
            $option_order++;
        }
        $question_order++;
    }
    return $list_of_options;
}

function prepare_result_message($decoded_json, $selected_values) {
    /**Prepare the HTML email response.

    @param decoded_json: JSON dataclass with all questions and answers.
    @param selected_values: Options selected by user.
    @return: HTML code of response
    */
    $questions_code = "";
    $total_score = 0;
    $question_order = 0;
    foreach ($decoded_json->questionnaire as $question) {
        // Score of the user
        $score = 0;
        $nr_of_correct_options = 0;

        $html_question = render_html_question($question->question, 1 + $question_order);
        $option_order = 0;
        foreach ($question->options as $option) {
            $option_def_str = "option_" . $question_order . "_" . $option_order;
            $correct = $option->correct;
            $user_selected = false;
            // Check if user selected this option:
            if (in_array($option_def_str, $selected_values)) {
                $user_selected = true;
            }
            // Parse options
            if ($correct and $user_selected) {
                $html_question .= render_html_option_correct($option->option, 1 + $option_order);
                $score++;
                $nr_of_correct_options++;
            }
            else if ($correct and !$user_selected) {
                $html_question .= render_html_option_unselected_correct($option->option, 1 + $option_order);
                $nr_of_correct_options++;
            }
            else if (!$correct and $user_selected) {
                $html_question .= render_html_option_incorrect($option->option, 1 + $option_order);
                $score--;
            }
            else if (!$correct and !$user_selected) {
                $html_question .= render_html_option_others($option->option, 1 + $option_order);
            }
            $option_order++;
        }
        $question_order++;

        // Evaluate score:
        if ($score <= 0) {
            $score = 0;
        }
        else {
            $score /= $nr_of_correct_options;
        }
        $questions_code .= render_question_options_wrapper($html_question, $score);
        $total_score += $score;
    }
    $html_code = render_all_questions_wrapper($questions_code, $total_score, $question_order);

    return $html_code;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Encapsulate incoming data
    $data = array();

    // Decode incoming JSON
    $json_content = file_get_contents($LINK_TO_JSON);
    $json_decoded = json_decode($json_content);

    $question_post_keys = get_list_of_post_keys($json_decoded);
    $expected = array_merge(array('address_of_user', 'name_of_user'), $question_post_keys);

    // List of all selected values by user
    $selected_values = [];

    foreach ($_POST as $key => $value) {
      if (!in_array($key, $expected)) {
        header('HTTP/1.1 400 Bad Request', false, 400);
        exit(json_encode(array("message" => "Wrong post variables!")));
      }
      if (in_array($key, $question_post_keys)) {
        // Check restricted values
        if(!in_array($value, $expected)) {
            header('HTTP/1.1 400 Bad Request', false, 400);
            exit(json_encode(array("message" => "Wrong post variables!")));
        }
        else {
            array_push($selected_values, $value);
        }
      }
      if (strip_tags($value) != $value) {
        header('HTTP/1.1 400 Bad Request', false, 400);
        exit(json_encode(array("message" => "Wrong post values!")));
      }
      $data[$key] = $value;
    }

    // Process values
    $address = $data['address_of_user'];
    $name_of_user = $data['name_of_user'];

    if(!filter_var($address, FILTER_VALIDATE_EMAIL)){
        // Incorrect email
        header('HTTP/1.1 400 Bad Request', false, 400);
        exit(json_encode(array("message" => "Wrong email format!")));
    }

    if(strlen($name_of_user) == 0){
        // Incorrect email
        header('HTTP/1.1 400 Bad Request', false, 400);
        exit(json_encode(array("message" => "Wrong name format!")));
    }

    // Return 200 if OK
    header('HTTP/1.1 200 OK', false, 200);
    //exit(json_encode(array("message" => $SUCCESS_MESSAGE)));

    exit(json_encode(array("message" => prepare_result_message($json_decoded, $selected_values))));
}
