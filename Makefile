up:
\tdocker compose up -d

down:
\tdocker compose down

build:
\tdocker compose up -d --build

logs:
\tdocker compose logs -f

bash:
\tdocker compose exec app bash

migrate:
\tdocker compose exec app php bin/console doctrine:migrations:migrate -n

fixtures:
\tdocker compose exec app php bin/console doctrine:fixtures:load -n

reset-db:
\tdocker compose exec app php bin/console doctrine:database:drop --force
\tdocker compose exec app php bin/console doctrine:database:create
\tdocker compose exec app php bin/console doctrine:migrations:migrate -n
\tdocker compose exec app php bin/console doctrine:fixtures:load -n

test:
\tdocker compose exec app php bin/phpunit
