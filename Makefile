SHELL := /bin/bash

init:
	docker run --rm -u "$$(id -u):$$(id -g)" -v "$$(pwd):/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
	cp .env.example .env
	./vendor/bin/sail up -d --build
	./vendor/bin/sail artisan key:generate
	./vendor/bin/sail artisan migrate:fresh --seed
	./vendor/bin/sail npm install
	./vendor/bin/sail npm run dev
