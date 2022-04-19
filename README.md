## League-manager

Installation
1. docker-compose up
2. docker-compose exec php-fpm bash 
3. cd league-manager
4. composer install
5. import league_manager.dump and league_manager_test.dump in postgres


Commands:
* app:initial-populate
* app:simulate-season {id}
* app:watch-final-score-change

Tests:
vendor/bin/simple-phpunit
