<img src="demo_files/logo.png" alt="Questiroud" />

# Questiroud: a simple application for quiz
Author: David Salac <https://github.com/david-salac>

Questiround is the application for generation of questionnaires,
implementing both front-end and back-end parts with an evaluation
of answers send on an email.

## What is Questiroud about?
Questiroud allows you to generate a simple questionnaire on your
websites. Each question with its answers is defined in the JSON
file with a simple configuration. E-mail and name of the user are
collected in the next step. Simple PHP script (that can be hosted
on the cheapest hosting) then evaluate questionnaire and send
information to the user e-email (plus store information in your
local storage as CSV file). 

### How it works technically?
Front-end of Questiroud uses the simple jQuery library. To include
a questionnaire, you can easily copy some tags (and styles together
with JavaScript files) to your web-page - remaining part is done
automatically.

Back-end uses the PHP script with a simple configuration. It
process questionnaire, send an e-mail with results to user and
store information to CSV file. It uses the most straightforward
PHP script that can be deployed to almost every available PHP
web-hosting (including the really cheap ones).

## How to configure Questiroud
To configure Questiroud you need to know the following information.

### Defining questions and answers for questionnaire
First, you need to set-up questions. It has to be in JSON
files with the following logic:
```
{
    "questionnaire": [
        {
            // The question definition
            "question": STRING,
            // Type of question
            // If 'one_correct' - only one answer can be selected
            // If 'multiple_correct' - many answers can be selected
            "type": ONE_OF("one_correct", "multiple_correct"),
            // Is the answer required  
            "required": BOOLEAN,
            "options": [
                // List of options following the logic:
                {
                    // The option itself (text of option)
                    "option": STRING,
                    // Is this option correct answer to question?
                    "correct": BOOLEAN
                },
                // ... next options
            ]
        },
        // ... next questions
    ]
}
```
### Configure your server
You need to copy somewhere on your server file `app/index.php`
and make it accessible (and know the URL how to access it).
After you copy it somewhere (using FTP or whatever), check the
URL by accessing it in the browser - you will receive some message
looking like `{"message":"No data!"}` - if you can see it, it all OK.

### Configure e-mail structure
If you wish to change the e-mail that is sent to the user, check
the file `app/index.php`. On the beginning of the file, there is the
configuration section that allows you to rewrite each part of
a message that is sent to the user.

### Configure front-end part
First, you need to copy the code of Questiroud to your code. Use the
logic from `index.html` file. There is the part that imports jQuery
and styles in the header (the first one is required). Then, there
is the part importing Questiroud script itself.

Crucially, you need to configure the connection to the back-end in
the `questiroad.js` file. There are two variables `JSON_URL` and
`TARGET_POST_URL`. First one must be a link to JSON file and
another one the link to the back-end index.php script.

If you wish to modify messages (texts) inside the questionnaire,
there is a simple way - modify variables on the top of
`questiroad.js` file if needed.

## Running Questiroud back-end locally
To build and run application using PHP Docker images use commands:
```
cd docker
# Build Docker images of PHP that can run the Questiroud
docker build -t questiroud .
cd ..
# Bind the /app folder with the PHP application folder in Docker => makes development faster
docker run -d -it -p 5560:80 --mount 'type=bind,src='$(pwd)'/app,dst=/var/www/site' questiroud
```
The starting folder has to be the application folder. The `index.php`
has to be in debugging mode (set variable `DEBUG` to true, it is on
the top of the file).

After you do so, your back-end is now running on the address:
```
http://localhost:5560/
```
