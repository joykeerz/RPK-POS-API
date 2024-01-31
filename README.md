Untuk setup di local environment, mohon ikuti command berikut :

git clone https://github.com/joykeerz/RPK-POS-API.git
composer update
composer upgrade
composer install
php artisan migrate:fresh
php artisan db:seed
php artisan serve
