# SymfonyRabbit

A simple application for sending emails via the SMTP protocol and RabbitMQ.


You will need running MySQL (with empty database) and RabbitMQ servers.

How to start:
1. Prepare your .env file with your MySQL, RabbitMQ and SMTP credentials.
2. Run following CMD commands in the project directory:
  - composer install
  - php bin/console doctrine:migrations:migrate
  - php bin/console rabbitmq:setup-fabric
  - php bin/console rabbitmq:consumer emailing
  - php bin/console server:start
3. Prepare your phpunit.xml file with your MySQL and RabbitMQ credentials

http://127.0.0.1:8000/
