#!/bin/sh
set -e

docker-compose -f docker-compose.yml -f docker-compose.override.yml -f docker-compose.test.yml up -d --build --wait
docker-compose -f docker-compose.yml -f docker-compose.override.yml -f docker-compose.test.yml exec php bin/console doctrine:database:create --if-not-exists
docker-compose -f docker-compose.yml -f docker-compose.override.yml -f docker-compose.test.yml exec php bin/console doctrine:migrations:migrate --no-interaction
docker-compose -f docker-compose.yml -f docker-compose.override.yml -f docker-compose.test.yml exec php bin/console hautelook:fixtures:load --no-interaction
docker-compose -f docker-compose.yml -f docker-compose.override.yml -f docker-compose.test.yml exec php bin/codecept run tests/functional/ProductOfferingsTest.php
docker-compose -f docker-compose.yml -f docker-compose.override.yml -f docker-compose.test.yml down

exit 1
