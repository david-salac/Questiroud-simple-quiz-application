<?php
/* =================== MAIN CONFIGURATION ================================== */
define("LINK_TO_JSON", "questions.json");  // Link to JSON file (or URL)
define("DEBUG_MODE", false); // For running locally only (does not generate email and file, just print email body in response)
define("ALLOW_SAME_ORIGIN", true); // Add the header 'Access-Control-Allow-Origin: *'
define("FILE_PATH_TO_EXPORT", "results.csv"); // Path to file where information about user are exported
define("EMAIL_COPY_E_MAILS", array());  // Where to send copy of email
/* ========================================================================= */

/* ====================== EMAIL CONFIGURATION ============================== */
define("EMAIL_SUBJECT", "Evaluation of questionnaire");  // Subject of the email that is send
define("EMAIL_FROM_NAME", "Questiroud analysis");  // From who (name) it comes
define("EMAIL_FROM_E_MAIL", "info@example.com");  // From who (e-mail) it comes
define("EMAIL_REPLY_E_MAIL", "info@example.com");  // To what email reply
/* ========================================================================= */

/* ====================== CONTENT DEFINITION =============================== */
define("ADDRESSING_PREFIX", "Dear "); // How do you wish to address user
define("ADDRESSING_SUFFIX", ",<p>Congratulation, you have successfully finished your questionnaire. The following overview analyses your performance.</p>"); // Suffix after addressing (technically everything between content and addressing)
define("CONTENT_SUFFIX", "<p>Questiroud is the application for generation of questionnaires, implementing both front-end and back-end parts with an evaluation of answers send on an email.</p>");  // Technically the last paragraph of email
define("QUESTION_SCORE_PREFIX", "Score for question: "); // The prefix for score of a question
define("QUESTION_SCORE_SUFFIX", " point."); // Suffix after score for each question
define("TOTAL_SCORE_PREFIX", "Total score: ");  // Total score prefix
define("TOTAL_SCORE_SUFFIX", " percent."); // Total score suffix
define("SUCCESS_MESSAGE", "Your questionnaire has been evaluated, please check your email!"); // Message that is shown if everything goes well
/* ========================================================================= */

/* ========================= CSS STYLES FOR ELEMENTS ======================= */
define("QUESTION_STYLE", "margin: 0 0 5px 0; font-size: 110%;"); // Style for a question text
define("OPTION_CORRECT_STYLE", "background: #00C521; padding: 15px; box-sizing: border-box;"); // Style for the option that is correct and correctly selected
define("OPTION_INCORRECT_STYLE", "background: #FEA4A4; padding: 15px; box-sizing: border-box;"); // Style for the option that is incorrectly selected
define("OPTION_CORRECT_UNSELECTED_STYLE", "background: #B4F0BE; padding: 15px; box-sizing: border-box;"); // Style for the option that is correct but not selected
define("OPTION_OTHER_STYLE", "background: #dedede; padding: 15px; box-sizing: border-box;"); // Style for the option that is incorrect and not selected (every other option)
define("QUESTION_WRAPPER_STYLE", "background: #F5F5F5; padding: 15px 20px 15px 20px; box-sizing: border-box; margin-bottom: 20px;"); // Style for the wrapper for one question with options
define("QUESTION_SCORE_STYLE", "margin-bottom: 0; margin-top: 10px; padding-bottom: 0;"); // Style for the score of a question
define("ALL_QUESTIONS_WRAPPER_STYLE", "padding: 15px; box-sizing: border-box; border: 1px solid #EAEAEA; border-radius: 5px; margin-top: 15px; margin-bottom: 15px"); // Style for the wrapper of all questions
define("TOTAL_SCORE_STYLE", "margin: 0;"); // Style for the total score sequence
define("CONTENT_WRAPPER_STYLE", "background: #ffffff; padding: 20px; box-sizing: border-box;"); // Style for the wrapper of the whole content
define("LAYOUT_STYLE", "margin: auto; width: 90%; max-width: 900px; font-family: sans-serif;"); // Style defining layout and general styles
/* ========================================================================= */

/* ========================== ERROR MESSAGES =============================== */
define("ERROR_MAIL_SENDING", "There was a problem with e-mail sending!"); // Message when mail function fails
define("ERROR_FILE_OPEN", "Cannot open file!"); // Message when file opening fails
define("ERROR_POST_KEYS", "Wrong post keys passed!"); // Message when there are wrong post keys
define("ERROR_POST_VALUES", "Wrong post values passed!"); // Message when there are wrong post values
define("ERROR_E_MAIL", "Wrong e-mail format!"); // Message when e-mail format is wrong
/* ========================================================================= */


/* ===================== SETTING HEADERS =================================== */
if (ALLOW_SAME_ORIGIN) {
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
    return '<div style="'.QUESTION_WRAPPER_STYLE.'">'.$question_plus_options.'<p style="'.QUESTION_SCORE_STYLE.'">'.QUESTION_SCORE_PREFIX.number_format($score, 2).QUESTION_SCORE_SUFFIX."</p></div>";
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
    return json_encode(array("message" => ERROR_FILE_OPEN));
}
function write_to_file($name_of_user, $address_of_user, $total_score) {
    /**Save information about user to file.
    @param name_of_user: name that user mentioned.
    @param address_of_user: e-mail address of user.
    @param total_score: total achieved score.
    */
    // Clean saved fields
    // 1) No ',' symbol
    $save_name = str_replace(',', '-', $name_of_user);
    $save_email = str_replace(',', '-', $address_of_user);
    // 1) No '\n' symbol
    $save_name = str_replace("\n", " ", $save_name);
    $save_email = str_replace("\n", " ", $save_email);

    $total_score_string = number_format(100 * $total_score, 2);

    $csv_file;
    if (!file_exists(FILE_PATH_TO_EXPORT)) {
        $csv_file = fopen(FILE_PATH_TO_EXPORT, "w+") or die(file_open_error());
        fwrite($csv_file, "USER_NAME,USER_EMAIL,TIME,TOTAL_SCORE_PERCENT\n");
    }
    else {
        $csv_file = fopen(FILE_PATH_TO_EXPORT, "a") or die(file_open_error());
    }
    // Get current time
    $time_now = time();
    // Write result
    fwrite($csv_file, $save_name.",".$save_email.','.date("Y-m-dTH:i:s",$time_now).','.$total_score_string."\n");
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
    $headers.= "Content-Transfer-Encoding: base64\r\n";
    $headers.= "Reply-To: ".EMAIL_REPLY_E_MAIL."\r\n";
    $headers.= "X-Mailer: PHP/" . phpversion();
    if (!mail($email_to, $email_subject, base64_encode($email_body), $headers)) {
        header('HTTP/1.1 500 Internal Server Error', false, 500);
        return json_encode(array("message" => ERROR_MAIL_SENDING));
    }
}
// ============================================================================

/* ========== PART FOR EVALUATION AND CONTENT PREPARATION ================== */
function get_list_of_post_keys($decoded_json) {
    /**Return array of all acceptable values in JSON for POST keys related to
    questionnaire answers.
    @param decoded_json: JSON dataclass with all questions and answers.
    @return: array of all answers.
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
    @param selected_values: options selected by user.
    @param name_of_user: name of the user (filled when post).
    @param address_of_user: e-mail address of user.
    @return: array with indices 0) HTML code of response, 1) total score
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
    return array($whole_layout, $total_score / $question_order);
}
// ============================================================================

/* ================== REST-ful HANDLING OF REQUEST ========================= */
function security_validation($value) {
    /**Validate if the value does not contain un-secure characters and if
    the length looks realistic (up to 128 characters and at least one).
    @param $value: string value to be checked.
    @return: true if all is good; false if there is an error.
    */
    if (strip_tags($value) != $value) {  // Not HTML tags
        return false;
    } if (false !== strpos($value, '"')) {  // No " character
        return false;
    } if (false !== strpos($value, "'")) {  // No ' character
        return false;
    } if (strlen($value) > 128) {  // No string bigger than 128 characters
        return false;
    } if (strlen($value) < 1) {  // No string shorter than 1 character
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
        exit(json_encode(array("message" => ERROR_POST_KEYS)));
      }
      if (in_array($key, $question_post_keys)) {
        // Check restricted values
        if(!in_array($value, $expected)) {
            header('HTTP/1.1 400 Bad Request', false, 400);
            exit(json_encode(array("message" => ERROR_POST_KEYS)));
        }
        else {
            array_push($selected_values, $value);
        }
      }
      if (!security_validation($value)) {
        header('HTTP/1.1 400 Bad Request', false, 400);
        exit(json_encode(array("message" => ERROR_POST_VALUES)));
      }
      $data[$key] = $value;
    }

    // Process values
    $address = $data['address_of_user'];
    $name_of_user = $data['name_of_user'];

    if(!filter_var($address, FILTER_VALIDATE_EMAIL)) {
        // Incorrect email
        header('HTTP/1.1 400 Bad Request', false, 400);
        exit(json_encode(array("message" => ERROR_E_MAIL)));
    }

    // Generate email HTML code
    $score_plus_email = prepare_result_message($json_decoded, $selected_values, $name_of_user, $address);
    $email_body_text = $score_plus_email[0];
    $total_achieved_score = $score_plus_email[1];

    if (!DEBUG_MODE) { // Send email and store info to file
        // Save info about user to file (append if exist)
        write_to_file($name_of_user, $address, $total_achieved_score);
        // Send email to user
        send_mail_in_utf_8($address, $email_body_text);
        foreach (EMAIL_COPY_E_MAILS as $copy_email_address) {
            // Send email to requested copy receiver
            send_mail_in_utf_8($copy_email_address, $email_body_text);
        }
        // Return 200 if OK
        header('HTTP/1.1 200 OK', false, 200);
        exit(json_encode(array("message" => SUCCESS_MESSAGE)));
    } else {
        // DEBUG (local) mode
        header('HTTP/1.1 200 OK', false, 200);
        // Return the content of the mail in response
        exit(json_encode(array("message" => $email_body_text)));
    }
}

// In the case that nothing is post array
header('HTTP/1.1 400 Bad Request', false, 400);
exit(json_encode(array("message" => "No data!")));
// ============================================================================
