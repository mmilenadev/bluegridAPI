# Bluegrid API

API platform for Bluegrid project.

Developed in [Symfony 7][1] and [API Platform][2] frameworks, using [Open API Specification v3.1][3] standardization for Rest API.

### Tech stack
- [PHP v8.2][4] (Web scripting language)
- [PostgreSQL v15.3][5] (Object-Relational Database)
- [NGINX v1.21][6] (Web server)
- [Docker v27][7] (Platform for developing, shipping and running applications)
- [Docker Compose v2][8] (Orchestration and Docker container management)
- [Symfony v7][1] (Framework for building web applications)
- [API Platform][2] (Implementing API architecture)
- [Open API Specification v3.1][3] (API standardization specification)

## Setup

### Requirements

To be able to set up and work with application it is required to have the following applications installed on your development machine:
- Docker
- Docker compose

In order to set up your local development environment execute following commands.

### Build Docker containers

To create Docker container execute following command from project root directory:

    docker-compose up -d --build

Enter php container to continue setup:

    docker-compose exec -u bluegrid php bash

### Setup environment variables

Create local environment file by copying default one, and populating it with local development variables.

    cp .env .env.local

### Install dependencies

Install dependencies using [Composer][9] by executing following command inside php container:

    composer install

### Create database schema

Create database schema and populate it by executing following commands in php container:

    bin/console do:mi:mi

### Update hosts

Update your local hosts (/etc/hosts on unix systems) file with following configuration:

    127.0.0.1 bluegrid-local.com

# Licence


[1]: https://symfony.com
[2]: https://api-platform.com
[3]: https://swagger.io/docs/specification/about
[4]: https://www.php.net
[5]: https://www.postgresql.org
[6]: https://www.nginx.com
[7]: https://docker.com
[8]: https://github.com/docker/compose
[9]: https://getcomposer.org





