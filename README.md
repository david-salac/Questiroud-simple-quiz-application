To build and run docker follow:
```
docker build -t questionnaire .
docker run -d -it -p 5560:80 --mount 'type=bind,src="$(pwd)"/app,dst=/var/www/site' questionnaire
```
