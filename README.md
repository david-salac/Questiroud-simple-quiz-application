# Questiroud: a simple application for quiz
Questiround is the application for generation of questionnaires,
implementing both front-end and back-end parts with an evaluation
of answers send on an email.

## Running Questiroud locally
To build and run docker follow:
```
docker build -t questionnaire .
docker run -d -it -p 5560:80 --mount 'type=bind,src='$(pwd)'/app,dst=/var/www/site' questionnaire
```
