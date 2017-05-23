# Remembrall
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/klapuch/Remembrall/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/klapuch/Remembrall/?branch=master) [![Build Status](https://travis-ci.org/klapuch/Remembrall.svg?branch=master)](https://travis-ci.org/klapuch/Remembrall) [![Build status](https://ci.appveyor.com/api/projects/status/jea6op0tyx3w9atm/branch/master?svg=true)](https://ci.appveyor.com/project/facedown/remembrall/branch/master) [![Coverage Status](https://coveralls.io/repos/github/klapuch/Remembrall/badge.svg?branch=master)](https://coveralls.io/github/klapuch/Remembrall?branch=master)

## Installation
### Clone
`git clone git@github.com:klapuch/Remembrall.git`
### Install dependencies
`composer install`
### Credentials
- `cp App/Configuration/.config.local.sample.ini App/Configuration/.config.local.ini`, change your credentials and disable https features
- `cp phinx.sample.yml phinx.yml`, change your credentials
### Docker
`docker-compose up`
#### Database
Create database and name it **remembrall** via `psql -U postgres -W` in **remembrall-postgres** image
##### Import
`cat fixtures/schema.sql | docker exec -i {IMAGE} psql -U postgres -d remembrall`
### Init
Go to `remembrall-server` image (`docker exec -it {IMAGE} /bin/bash`) and run `vendor/bin/phing init`
