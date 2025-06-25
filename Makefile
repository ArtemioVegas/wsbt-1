up:
	docker-compose -f docker-compose.yaml up -d
down:
	docker-compose -f docker-compose.yaml stop
logs:
	docker-compose -f docker-compose.yaml logs --tail=100 -f

composer-install:
	docker-compose exec php83-cli-container composer install

php-cs:
	docker exec -ti php83-cli-container composer fix-phpcs

stan:
	docker exec -ti php83-cli-container composer analyze-stan

phpmd:
	docker exec -ti php83-cli-container composer analyze-phpmd

test:
	docker exec -ti php83-cli-container composer test
