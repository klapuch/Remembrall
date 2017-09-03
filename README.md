# Remembrall
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/klapuch/Remembrall/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/klapuch/Remembrall/?branch=master) [![Build Status](https://travis-ci.org/klapuch/Remembrall.svg?branch=master)](https://travis-ci.org/klapuch/Remembrall) [![Build status](https://ci.appveyor.com/api/projects/status/jea6op0tyx3w9atm/branch/master?svg=true)](https://ci.appveyor.com/project/facedown/remembrall/branch/master) [![Coverage Status](https://coveralls.io/repos/github/klapuch/Remembrall/badge.svg?branch=master)](https://coveralls.io/github/klapuch/Remembrall?branch=master)

## What is it?
This is a simple project, which is built on my own created [libraries](https://packagist.org/packages/klapuch/), to prove myself, there is a different approach how to create applications.

## What it does?
Sends emails about changes on your subscribed pages. You type URL, CSS or XPath expression to observed part and interval for checks. In case of change, you are notified by email. 

## How to run it locally?
### Clone
`git clone git@github.com:klapuch/Remembrall.git`
### Dockerize
Start via docker compose:

`cd Remembrall && docker-compose -p remembrall up -d`
#### Configs
Basic config:

`cp App/Configuration/.config.local.sample.ini App/Configuration/.config.local.ini`, disable HTTPS features

Phinx config for migrations:

`docker exec -i remembrall_php-fpm_1 cp phinx.sample.yml phinx.yml`
#### Database
Create production database:

`docker exec -i remembrall-postgres psql -U postgres -c "CREATE DATABASE remembrall"`

Create test database:

`docker exec -i remembrall-postgres psql -U postgres -c "CREATE DATABASE remembrall_test"`

Import schema to production database:

`cat fixtures/schema.sql | docker exec -i remembrall-postgres psql -U postgres -d remembrall`

Import dump to production database:

`cat fixtures/dump.sql | docker exec -i remembrall-postgres psql -U postgres -d remembrall`

For signing in, password is **heslo**

Import schema to test database:

`cat Tests/TestCase/schema.sql | docker exec -i remembrall-postgres psql -U postgres -d remembrall_test`

#### Installation
Install all dependencies:

`docker exec -i remembrall_php-fpm_1 composer install`

Run linting, migrations and initialize assets:

`docker exec -i remembrall_php-fpm_1 vendor/bin/phing init`
