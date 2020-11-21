<?php
/* =================== MAIN CONFIGURATION ================================== */
define("LINK_TO_JSON", "questions.json");  // Link to JSON file (or URL)
define("DEBUG_MODE", true); // For running locally only
define("FILE_PATH_TO_EXPORT", "results.csv"); // Path to file where information about user are exported
/* ========================================================================= */

/* ====================== EMAIL CONFIGURATION ============================== */
define("EMAIL_SUBJECT", "Evaluation of questionnaire");  // Subject of the email that is send
define("EMAIL_FROM_NAME", "John Doe");  // From who (name) it comes
define("EMAIL_FROM_E_MAIL", "info@example.com");  // From who (e-mail) it comes
define("EMAIL_REPLY_E_MAIL", "info@example.com");  // To what email reply
/* ========================================================================= */

/* ====================== CONTENT DEFINITION =============================== */
define("ADDRESSING_PREFIX", "Dear ");
define("ADDRESSING_SUFFIX", ",<p>Here is the certificate from our quiz.</p>");
define("CONTENT_SUFFIX", "<p>End of the content.</p>");
define("QUESTION_SCORE_PREFIX", "Score for question: ");
define("QUESTION_SCORE_SUFFIX", ".");
define("TOTAL_SCORE_PREFIX", "Total score: ");
define("TOTAL_SCORE_SUFFIX", " percent.");
define("SUCCESS_MESSAGE", " Email sent!");
/* ========================================================================= */

/* ========================= CSS STYLES FOR ELEMENTS ======================= */
define("QUESTION_STYLE", "margin: 0;");
define("OPTION_CORRECT_STYLE", "background: #00C521; padding: 15px; box-sizing: border-box;");
define("OPTION_INCORRECT_STYLE", "background: #FEA4A4; padding: 15px; box-sizing: border-box;");
define("OPTION_CORRECT_UNSELECTED_STYLE", "background: #B4F0BE; padding: 15px; box-sizing: border-box;");
define("OPTION_OTHER_STYLE", "background: #dedede; padding: 15px; box-sizing: border-box;");
define("QUESTION_WRAPPER_STYLE", "background: #F5F5F5; padding: 15px 20px 15px 20px; box-sizing: border-box; margin-bottom: 20px;");
define("QUESTION_SCORE_STYLE", "margin-bottom: 0; padding-bottom: 0;");
define("ALL_QUESTIONS_WRAPPER_STYLE", "padding: 15px; box-sizing: border-box; border: 1px solid #EAEAEA; border-radius: 5px; margin-top: 15px");
define("TOTAL_SCORE_STYLE", "margin: 0;");
define("CONTENT_WRAPPER_STYLE", "background: #ffffff; padding: 20px; box-sizing: border-box;");
define("LAYOUT_STYLE", "margin: auto; width: 90%; max-width: 900px; font-family: sans-serif;");
/* ========================================================================= */

/* ===================== SETTING HEADERS =================================== */
if (DEBUG_MODE) {
    // To make localhost available (when running from docker)
    header('Access-Control-Allow-Origin: *', false);
}
// All as JSON
header('Content-type: application/json; charset=utf-8', false);
/* ========================================================================= */


// =================== RENDER FRACTION OF CODE FOR RESPONSE ===================
function render_html_question($question_text, $order) {
    /**Render question
    @param question_text: Text (string) of the question.
    @param order: Relative position (indexed from 1).
    @return: HTML string defining the question.
    */
    return '<h2 style="'.QUESTION_STYLE.'">'.$order.") ".$question_text."</h2>";
}
function render_html_option_correct($option_text, $order) {
    /**Correct answer selected by user
    @param option_text: Text (string) of the option.
    @param order: Relative position (indexed from 1).
    @return: HTML string defining the option.
    */
    return '<p style="'.OPTION_CORRECT_STYLE.'">'.$option_text."</p>";
}
function render_html_option_incorrect($option_text, $order) {
    /**Incorrect answer selected by user
    @param option_text: Text (string) of the option.
    @param order: Relative position (indexed from 1).
    @return: HTML string defining the option.
    */
    return '<p style="'.OPTION_INCORRECT_STYLE.'">'.$option_text."</p>";
}
function render_html_option_unselected_correct($option_text, $order) {
    /**Correct answer NOT selected by user
    @param option_text: Text (string) of the option.
    @param order: Relative position (indexed from 1).
    @return: HTML string defining the option.
    */
    return '<p style="'.OPTION_CORRECT_UNSELECTED_STYLE.'">'.$option_text."</p>";
}
function render_html_option_others($option_text, $order) {
    /**Incorrect unselected answer
    @param option_text: Text (string) of the option.
    @param order: Relative position (indexed from 1).
    @return: HTML string defining the option.
    */
    return '<p style="'.OPTION_OTHER_STYLE.'">'.$option_text."</p>";
}
function render_question_options_wrapper($question_plus_options, $score) {
    /**Wrapper for question plus option. Mainly for adding score to one
        question and visually separate questions.
    @param question_plus_options: single HTML question code.
    @param score: score received for answering this particular question.
    */
    return '<div style="'.QUESTION_WRAPPER_STYLE.'">'.$question_plus_options.'<h3 style="'.QUESTION_SCORE_STYLE.'">'.QUESTION_SCORE_PREFIX.number_format($score, 2).QUESTION_SCORE_SUFFIX."</h3></div>";
}
function render_all_questions_wrapper($all_questions, $total_score, $total_question_number) {
    /**Wrapper for all question plus options. Mainly for adding total score.
    @param all_questions: HTML code of questions.
    @param total_score: Total score of the user.
    @param total_question_number: Total number (count) of questions.
    */
    return '<div style="'.ALL_QUESTIONS_WRAPPER_STYLE.'">'.$all_questions.'<h2 style="'.TOTAL_SCORE_STYLE.'">'.TOTAL_SCORE_PREFIX.number_format(100 * $total_score/$total_question_number, 2).TOTAL_SCORE_SUFFIX."</h2></div>";
}
function render_content_wrapper($content, $name_of_user, $address_of_user) {
    /**Wrap the content of pages (technically all questions). Mainly for adding
        letter to user.
    @param content: HTML content of pages.
    @param name_of_user: Name of the user (like John).
    @param address_of_user: e-mail address of user.
    */
    return '<div style="'.CONTENT_WRAPPER_STYLE.'">'.ADDRESSING_PREFIX.$name_of_user.ADDRESSING_SUFFIX.$content.CONTENT_SUFFIX."</div>";
}
function render_layout_wrapper($page_content) {
    /**Wrap the page content for layout. Mainly for layout
    @param page_content: HTML content of pages.
    */
    return '<html><body><div style="'.LAYOUT_STYLE.'">'.$page_content."</div></body></html>";
}
// ============================================================================

/* ============ FUNCTIONALITY FOR WRITING TO FILE ========================== */
function file_open_error() {
    /**Return error message with correct status code if cannot open file.*/
    header('HTTP/1.1 500 Internal Server Error', false, 500);
    return json_encode(array("message" => "Cannot open file!"));
}
function write_to_file($name_of_user, $address_of_user) {
    /**Save information about user to file.
    @param name_of_user: name that user mentioned.
    @param address_of_user: e-mail address of user.
    */
    // Clean saved fields
    // 1) No ',' symbol
    $save_name = str_replace(',', '-', $name_of_user);
    $save_email = str_replace(',', '-', $address_of_user);
    // 1) No '\n' symbol
    $save_name = str_replace("\n", " ", $save_name);
    $save_email = str_replace("\n", " ", $save_email);

    $csv_file;
    if (!file_exists(FILE_PATH_TO_EXPORT)) {
        $csv_file = fopen(FILE_PATH_TO_EXPORT, "w+") or die(file_open_error());
        fwrite($csv_file, "USER_NAME,USER_EMAIL,TIME\n");
    }
    else {
        $csv_file = fopen(FILE_PATH_TO_EXPORT, "a") or die(file_open_error());
    }
    // Get current time
    $time_now = time();
    // Write result
    fwrite($csv_file, $save_name.",".$save_email.','.date("Y-m-dTH:i:s",$time_now)."\n");
    fclose($csv_file);
}
// ============================================================================

/* ================= FUNCTIONALITY FOR SENDING EMAIL ======================= */
function send_mail_in_utf_8($email_to, $email_body) {
    /**Send email in UTF-8 encoding.
    @param email_to: email address of receiver - to whom you send it.
    @param email_body: body of email (actual text/code).
    */
    $email_subject= "=?utf-8?b?".base64_encode(EMAIL_SUBJECT)."?=";
    $headers = "MIME-Version: 1.0\r\n";
    $headers.= "From: =?utf-8?b?".base64_encode(EMAIL_FROM_NAME)."?= <".EMAIL_FROM_E_MAIL.">\r\n";
    $headers.= "Content-Type: text/html;charset=utf-8\r\n";
    $headers.= "Reply-To: ".EMAIL_REPLY_E_MAIL."\r\n";
    $headers.= "X-Mailer: PHP/" . phpversion();
    mail($email_to, $email_subject, $email_body, $headers);
}
// ============================================================================

/* ========== PART FOR EVALUATION AND CONTENT PREPARATION ================== */
function get_list_of_post_keys($decoded_json) {
    /**Return list of all acceptable values in JSON for POST keys related
        to questionnaire answers.
    @param decoded_json: JSON dataclass with all questions and answers.
    @return: list of all answers.
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

function prepare_result_message($decoded_json, $selected_values, $name_of_user, $address_of_user) {
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
    $content = render_all_questions_wrapper($questions_code, $total_score, $question_order);
    $page_body = render_content_wrapper($content, $name_of_user, $address_of_user);
    $whole_layout = render_layout_wrapper($page_body);

    // Return whole HTML code
    return $whole_layout;
}
// ============================================================================

/* ================== REST-ful HANDLING OF REQUEST ========================= */
function security_validation($value) {
    /**Validate if the value does not contain un-secure characters.
    @param $value: string value to be checked.
    @return: true if all is good; false if there is an error.
    */
    if (strip_tags($value) != $value) {  // Not HTML tags
        return false;
    } if (false !== strpos($value, '"')) {  // No " character
        return false;
    } if (false !== strpos($value, "'")) {  // No ' character
        return false;
    }
    return true;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process the request.

    // Encapsulate incoming data
    $data = array();

    // Decode incoming JSON
    $json_content = file_get_contents(LINK_TO_JSON);
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
      if (!security_validation($value)) {
        header('HTTP/1.1 400 Bad Request', false, 400);
        exit(json_encode(array("message" => "Wrong post values!")));
      }
      $data[$key] = $value;
    }

    // Process values
    $address = $data['address_of_user'];
    $name_of_user = $data['name_of_user'];

    if(!filter_var($address, FILTER_VALIDATE_EMAIL)) {
        // Incorrect email
        header('HTTP/1.1 400 Bad Request', false, 400);
        exit(json_encode(array("message" => "Wrong email format!")));
    }

    if(strlen($name_of_user) == 0) {
        // Incorrect email
        header('HTTP/1.1 400 Bad Request', false, 400);
        exit(json_encode(array("message" => "Wrong name format!")));
    }

    if (!DEBUG_MODE) {
        // Save info about user to file (append if exist)
        write_to_file($name_of_user, $address);
        // Send email
        send_mail_in_utf_8($address, prepare_result_message($json_decoded, $selected_values, $name_of_user, $address));
        // Return 200 if OK
        header('HTTP/1.1 200 OK', false, 200);
        exit(json_encode(array("message" => SUCCESS_MESSAGE)));
    } else {
        // DEBUG (local) mode
        header('HTTP/1.1 200 OK', false, 200);
        // Return the content of the mail in response
        exit(json_encode(array("message" => prepare_result_message($json_decoded, $selected_values, $name_of_user, $address))));
    }
}

// In the case that nothing is post
header('HTTP/1.1 400 Bad Request', false, 400);
exit(json_encode(array("message" => "No data!")));
// ============================================================================
