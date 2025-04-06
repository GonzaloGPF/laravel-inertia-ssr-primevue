# Laravel + Inertia SSR + PrimeVue
Boilerplate repository for fast start

## Repository
Repository is in [BitBucket](https://github.com/GonzaloGPF/laravel-inertia-ssr-primevue)
Clone the repository with `git clone git@github.com:GonzaloGPF/laravel-inertia-ssr-primevue.git`

## Env variables
Create **.env** file. You can use **.env.example** file as a boilerplate.

## Local Development
Local development is configured to use [Laravel Sail](https://laravel.com/docs/10.x/sail), it means you should not
install anything in your machine.

You only need to install [Docker](https://www.docker.com/).

To set up the project for the first time you will need to install dependencies without having installed Laravel Sail.
For that, you can use following command:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

After that, you normally will use `vendor/bin/sail up -d` and `vendor/bin/sail down`.

If you need to remove everything (volumes, images and networks) from docker you can use:

```bash
docker-compose down --rmi all
```
If you just want to remove volumes you can use:
```bash
sail down -v
```

## Server Requirements
Your development environment will need the following extra software:

- All Laravel [requirements](https://laravel.com/docs/10.x/deployment#server-requirements)

> If using sail, run `sail build` and remember to restart service

## Installation
This project is ready for local development using Laravel Sail, it means you will need Docker installed.
You can check out Docker configuration at **docker-compose.yml** file.

As first step, you will need to run following commands
> Remember to init Docker before running commands

- `sail php artisan key:generate`
- `sail php artisan migrate --seed`
- `sail yarn`

## Daily usage
Daily usage will require you to type following commands:
- Init web server with `vendor/bin/sail up -d`
- Compile front with `vendor/bin/sail yarn dev`

### Alias Sail
You can create an alias to simplify the command above.

```bash
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

By this way, your daily usage will remain with commands `sail up -d` and `sail down`

### Extra alias
```bash
alias sail-dev='sail up -d && sail yarn dev'
```

By this way, your daily usage will be only `sail-dev` command

## Database
To rebuild database
`php artisan migrate:fresh`

To seed database will fake data
`php artisan db:seed`

You also can rebuild and seed database at the same time with
`php artisan migrate:fresh --seed`

## Troubleshoot
Sometimes **.env** fails with error `./.env: line 71: $'\r': command not found`,
to fix that you can `sed -i 's/\r$//' .env`
