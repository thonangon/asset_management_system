

```bash
composer require laravel/boost --dev

php artisan boost:install
```
+Backend setup
-composer i or composer install
-copy .env.example ->.env 
-include database connect :
Example : DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=asset_management_system
DB_USERNAME=root
DB_PASSWORD=
-php artisan key:generate
-php artisan migrate
-php artisan db:seed (tempary data for user demo)
-php artisan serve 
