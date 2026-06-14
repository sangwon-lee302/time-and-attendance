SHELL := /bin/bash

init:
	docker run --rm -u "$$(id -u):$$(id -g)" -v "$$(pwd):/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
	cp .env.example .env
	./vendor/bin/sail up -d --build
	./vendor/bin/sail artisan key:generate
	@echo "Waiting for database to be ready..."
	@until nc -z -v -w3 127.0.0.1 3306; do \
		@echo "Database is still booting, retrying in 2 seconds"; \
        sleep 2; \
    done
	@echo "Database is up. Running migrations"
	./vendor/bin/sail artisan migrate:fresh --seed
	./vendor/bin/sail npm install
	./vendor/bin/sail npm run build
