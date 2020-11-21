/* ================== CONFIGURATION SECTION ================================ */
// Code configuration
var JSON_URL = "http://localhost:5560/questions.php";
var TARGET_POST_URL = "http://localhost:5560/";

// Text parts
var NEXT_BUTTON_TEXT = "NEXT";
var SUBMIT_BUTTON_TEXT = "SUBMIT";
var PLACEHOLDER_EMAIL = "Your email";
var PLACEHOLDER_NAME = "Your name";
var SENDING_STATUS = "We are processing your request, please wait!";
var ERROR_MESSAGE_FOR_QUOTATION_MARKS_IN_NAME = "Your name and email must not contain quotation marks character.";
var NOT_ALL_REQUIRED_QUESTIONS_ANSWERED = "You have to answer all required questions!";

// CSS classes
var CLASS_NEXT_BUTTON = "questiroud_next_button";
var CLASS_QUESTION_WRAPPER = "questiroud_single_question";
var CLASS_SUBMIT_BUTTON = "questiroud_submit_button";
var CLASS_FOR_QUESTION = "questiroud_question_header";
var CLASS_FOR_OPTION_INPUT = "questiroud_some_option";
var CLASS_FOR_LABEL = "questiroud_some_label";
var CLASS_OPTIONAL_QUESTION = "questiroud_optional_question";
var CLASS_REQUIRED_QUESTION = "questiroud_required_question";
var CLASS_EMAIL_INPUT = "questiroud_user_email";
var CLASS_NAME_INPUT = "questiroud_user_name";

// Included parts of code
var TAG_FOR_QUESTION = "span";
var TAG_QUESTION_WRAPPER = "div";
var CODE_BELOW_EMAIL_INPUT = "";
var CODE_ABOVE_EMAIL_INPUT = "";
var CODE_ABOVE_NAME_INPUT = "";
var CODE_BELOW_NAME_INPUT = "";
var SEPARATOR_QUESTION_OPTIONS = '<div class="questiroud-clear"></div>';
var SEPARATOR_QUESTIONS = "";
var SEPARATOR_QUESTIONS_SUBMIT = "";
var SEPARATOR_OPTIONS = "";
/* ========================================================================= */

function add_question(question, question_i, is_last) {
    /**Append question (with all options)
    @param question: dictionary containing info aboug question (+ answers).
    @param question_i: Relative position of question among other (integer > 0).
    @param is_last: if true, the question is last in the series, false otherwise.
    @return: HTML code with one question and its wrapper
    */

    var question_classes = CLASS_FOR_QUESTION
    if (question['required']) {
        question_classes += " " + CLASS_REQUIRED_QUESTION;
    }
    else {
        question_classes += " " + CLASS_OPTIONAL_QUESTION;
    }
    // Variable containing whole HTML code for question.
    var questiroud_question = ("<" + TAG_QUESTION_WRAPPER + " class=\"" + CLASS_QUESTION_WRAPPER + "\"/>");

    questiroud_question += "<" + TAG_FOR_QUESTION + " class=\"" + question_classes + "\">" + (question_i + 1) + ") " + question['question'] + "</" + TAG_FOR_QUESTION + ">";
    questiroud_question += SEPARATOR_QUESTION_OPTIONS;
    // Append options
    var id_option = 'option_' + question_i;

    // List of all options (to check if anything is checked later)
    var list_of_options = [];
    for (opt_i = 0; opt_i < question['options'].length; opt_i++) {
        var id_and_value = 'option_' + question_i + '_' + opt_i;
        list_of_options.push(id_and_value);

        var input_type = "radio";
        if (question['type'] == "multiple_correct") {
            questiroud_question += '<input class="' + CLASS_FOR_OPTION_INPUT + '" id="' + id_and_value + '" type="checkbox" name="' + id_and_value + '" value="' + id_and_value + '">';
        }
        else if (question['type'] == "one_correct") {
            questiroud_question += '<input class="' + CLASS_FOR_OPTION_INPUT + '" id="' + id_and_value + '" name="' + id_option + '" type="radio" value="' + id_and_value + '">';
        }

        questiroud_question += '<label class="' + CLASS_FOR_LABEL + '" for="' + id_and_value + '">' + question['options'][opt_i]['option'] + '</label>';
        questiroud_question += SEPARATOR_OPTIONS;
    }
    if (!is_last) {
        questiroud_question += SEPARATOR_QUESTIONS;
    } else {
        questiroud_question += SEPARATOR_QUESTIONS_SUBMIT;
    }

    // Create a list of required values
    // Prefix for new values
    var prefix_validation = $("#validation").val() + ";";
    if ($("#validation").val() == "") {
        prefix_validation = "";
    }
    if (question['required']) {
        $("#validation").val(prefix_validation + list_of_options.join(","));
    }
    else {
        $("#validation").val(prefix_validation + "");
    }

    // End question wrapper tag.
    questiroud_question += "</" + TAG_QUESTION_WRAPPER + ">"
    $('#questiroud-quiz').append(questiroud_question);
}

function render_questionnaire(json) {
    // Manage content sequence
    $("#questiroud-quiz").show();
    $("#questiroud-quiz-wrong").hide();
    $("#questiroud-contact").hide();
    $("#questiroud-contact-wrong").hide();
    $("#questiroud-response").hide();
    $("#questiroud-waiting").hide();
    $("#questiroud-response-wrong").hide();

    for (qu_i = 0; qu_i < json['questionnaire'].length; qu_i++) {
        var this_is_last = false;  // Indicate last question in series
        if (qu_i == json['questionnaire'].length - 1) {
            this_is_last = true;
        }
        add_question(json['questionnaire'][qu_i], qu_i, this_is_last);
    }
    $('#questiroud-quiz').append('<input class="' + CLASS_NEXT_BUTTON + '" type="submit" value="' + NEXT_BUTTON_TEXT + '">');
}

function validate_questionnaire() {
    /** Check if all answers are answered.
    */
    var _array_of_questions = [];
    _array_of_questions.push($("#validation").val().split(";"));
    var array_of_questions = _array_of_questions[0];
    for (quest_idx = 0; quest_idx < array_of_questions.length; quest_idx++) {
        if (array_of_questions[quest_idx].length == 0) {
            // Optional option (do not have to select)
            continue;
        }
        var _array_of_values = [];
        _array_of_values.push(array_of_questions[quest_idx].split(","));
        var array_of_values = _array_of_values[0];

        var error_in_answer = true;
        for (answ_idx = 0; answ_idx < array_of_values.length; answ_idx++) {
            var id_and_value = array_of_values[answ_idx];
            if ( $("#" + id_and_value).is(':checked')) {
                error_in_answer = false;
                break;
            }
        }
        if (error_in_answer) {
            return false;
        }
    }
    return true;
}

function render_contact() {
    // Manage content sequence
    $("#questiroud-quiz").hide();
    $("#questiroud-quiz-wrong").hide();
    $("#questiroud-contact").show();
    $("#questiroud-contact-wrong").hide();
    $("#questiroud-response").hide();
    $("#questiroud-waiting").hide();
    $("#questiroud-response-wrong").hide();

    $("#questiroud-contact").append(CODE_ABOVE_NAME_INPUT);
    $("#questiroud-contact").append('<input type="text" id="name_of_user" name="name_of_user" class="' + CLASS_NAME_INPUT + '" placeholder="' + PLACEHOLDER_NAME + '" required>');
    $("#questiroud-contact").append(CODE_BELOW_NAME_INPUT);

    $("#questiroud-contact").append(CODE_ABOVE_EMAIL_INPUT);
    $("#questiroud-contact").append('<input type="email" id="address_of_user" name="address_of_user" class="' + CLASS_EMAIL_INPUT + '" placeholder="' + PLACEHOLDER_EMAIL + '" required>');
    $("#questiroud-contact").append(CODE_BELOW_EMAIL_INPUT);
    $('#questiroud-contact').append('<input class="' + CLASS_SUBMIT_BUTTON + '" type="submit" value="' + SUBMIT_BUTTON_TEXT + '">');
}

function render_response_correct(response_message) {
    // Manage content sequence
    $("#questiroud-quiz").hide();
    $("#questiroud-quiz-wrong").hide();
    $("#questiroud-contact").hide();
    $("#questiroud-contact-wrong").hide();
    $("#questiroud-response").show();
    $("#questiroud-waiting").hide();
    $("#questiroud-response-wrong").hide();
    // Render content of response
    $("#questiroud-response").html(response_message);
}

function render_response_wrong(response_message) {
    // Manage content sequence
    $("#questiroud-quiz").hide();
    $("#questiroud-quiz-wrong").hide();
    $("#questiroud-contact").hide();
    $("#questiroud-contact-wrong").hide();
    $("#questiroud-response").hide();
    $("#questiroud-waiting").hide();
    $("#questiroud-response-wrong").show();
    // Render content of response
    $("#questiroud-response-wrong").html(response_message);
}

function render_waiting_for_response() {
    // Manage content sequence
    $("#questiroud-quiz").hide();
    $("#questiroud-quiz-wrong").hide();
    $("#questiroud-contact").hide();
    $("#questiroud-contact-wrong").hide();
    $("#questiroud-response").hide();
    $("#questiroud-waiting").show();
    $("#questiroud-response-wrong").hide();
    // Render content of response
    $("#questiroud-waiting").html(SENDING_STATUS);
}

function validate_email_name() {
    /*Check if there are no quotation marks in email or name*/
    if (String($("#address_of_user").val()).indexOf("'") != -1 || String($("#address_of_user").val()).indexOf('"') != -1) {
        return false;
    }
    if (String($("#name_of_user").val()).indexOf("'") != -1 || String($("#name_of_user").val()).indexOf('"') != -1) {
        return false;
    }
    return true;
}

function render_contact_wrong() {
    // Manage content sequence
    $("#questiroud-contact-wrong").show();
    // Render content of response
    $("#questiroud-contact-wrong").html(ERROR_MESSAGE_FOR_QUOTATION_MARKS_IN_NAME);
    $('html, body').animate({
        scrollTop: $("#questiroud-contact-wrong").offset().top
    }, 1000);
}

$( document ).ready(function() {
    $.getJSON(JSON_URL, function(json) {
        render_questionnaire(json);
    });
});
$("#questiroud").submit(function( event ) {
    event.preventDefault();
    if($("#questiroud-contact").is(":hidden") && $("#questiroud-response").is(":hidden")){
        if (validate_questionnaire()) {
            // Second step, show contact
            render_contact();
        } else {
            $("#questiroud-quiz-wrong").html(NOT_ALL_REQUIRED_QUESTIONS_ANSWERED);
            $("#questiroud-quiz-wrong").show();
            $('html, body').animate({
                scrollTop: $("#questiroud-quiz-wrong").offset().top
            }, 1000);
        }
    }
    else if($("#questiroud-quiz").is(":hidden") && $("#questiroud-response").is(":hidden")){
        if (!validate_email_name()) {
            render_contact_wrong();
        } else {
            // Render waiting for response from server message
            render_waiting_for_response();

            // Remove validation field (no reason to send it to server)
            $("#validation").remove();

            // Send data to server
            $.post(
                TARGET_POST_URL,
                $("#questiroud").serialize(),
                function(response, status) {
                    // Render correct response (message)
                    render_response_correct(response['message']);
                }).fail(
                    function(response) {
                        // Show error message
                        render_response_wrong(response.responseJSON['message']);
                    }
                )
        }
    }
});