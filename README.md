## Ploutos Services

Backend APIs for Ploutos services


## Stack
- MySQL 5.7
- PHP 7.2

## Getting started
- clone repo
- `cd [into directory]` 
- `composer install` install dependencies  
- `cp .env.example .env`
- `php artisan key:generate` generate application key if not set
- edit .env file with local database info and application key
- `php artisan migrate` create database tables
- `php artisan passport:install` create encryption keys needed to generate secure access tokens

## Testing
- `php artisan test` run all tests
