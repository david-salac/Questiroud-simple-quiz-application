<img src="demo_files/logo.png" alt="Questiroud" />

# Questiroud: a simple application for quiz
Questiround is the application for generation of questionnaires,
implementing both front-end and back-end parts with an evaluation
of answers send on an email.

## Running Questiroud locally
To build and run application using PHP Docker images use commands:
```
cd docker
# Build Docker images of PHP that can run the Questiroud
docker build -t questiroud .
cd ..
# Bind the /app folder with the PHP application folder in Docker => makes development faster
docker run -d -it -p 5560:80 --mount 'type=bind,src='$(pwd)'/app,dst=/var/www/site' questiroud
```
The starting folder has to be the application folder.
