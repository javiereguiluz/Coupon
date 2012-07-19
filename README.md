# Coupon sample application #

Coupon is a sample application designed to learn Symfony2 development. It's a 
Groupon inspired clone, hence its name. Coupon application is explained in the 
*[Desarrollo web ágil con Symfony2](http://www.symfony.es/libro-symfony2/)* book
published by Javier Eguiluz at [symfony.es](http://symfony.es) (for now, this
book it's only published in Spanish).

If you find a bug, please fill in a bug report in the [Github issues page](https://github.com/javiereguiluz/Coupon/issues).

**Very important**: this application is used to teach how to develop real web
applications with Symfony2. Your *pull requests* are more than welcome, but please
keep always in mind that we don't care about abstraction layers, code purity,
and infinite scalability.

## Screenshots (click to enlarge) ##

### Frontend ###

[![Homepage](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-frontend-portada.png)](http://javiereguiluz.com/cupon/screenshots/cupon-frontend-portada.png)
[![Offer details](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-frontend-oferta.png)](http://javiereguiluz.com/cupon/screenshots/cupon-frontend-oferta.png)
[![Recent offers](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-frontend-recientes.png)](http://javiereguiluz.com/cupon/screenshots/cupon-frontend-recientes.png)
[![Login form](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-frontend-login.png)](http://javiereguiluz.com/cupon/screenshots/cupon-frontend-login.png)

### Extranet ###

[![Listing](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-extranet-listado.png)](http://javiereguiluz.com/cupon/screenshots/cupon-extranet-listado.png)
[![Offer edit action](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-extranet-modificar-oferta.png)](http://javiereguiluz.com/cupon/screenshots/cupon-extranet-modificar-oferta.png)

### Backend ###

[![Listing](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-backend-listado.png)](http://javiereguiluz.com/cupon/screenshots/cupon-backend-listado.png)
[![Offer show action](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-backend-ver-oferta.png)](http://javiereguiluz.com/cupon/screenshots/cupon-backend-ver-oferta.png)

## How to install ##

  1. `mkdir coupon`
  2. `git clone git://github.com/javiereguiluz/Coupon.git coupon`
  3. `cd coupon`
  4. `php bin/vendors install`
  5. `chmod -R 777 app/cache app/logs` (read [Setting up Permissions](http://symfony.com/doc/2.0/book/installation.html#configuration-and-setup) for a more elegant way to do this)
  6. Configure your web server
  7. Ensure that APC is installed and configured (it's used on the production environment)

## How to use ##

Before trying the application:

  1. Create a new sample database and configure its credentials in
     `app/config/parameters.ini` file (you can use `parameters.ini.dirs` file
     as reference)
  2. Create the database schema: `php app/console doctrine:schema:create`
  3. Initialize the ACL tables: `php app/console init:acl`
  4. Load data fixtures with the following commands:
    * `php app/console doctrine:fixtures:load` if you want to load the complete
      fixtures for the finished application (with all the ACL and security-related
      properties).  If you get *Truncating table with foreign keys fails* exception,
      execute the following command: `php app/console doctrine:fixtures:load --append`
    * `php app/console doctrine:fixtures:load --fixtures=app/Resources` if you
      want to load the simplified version of fixtures. Use this if you are
      developing the application and need simple fixtures without any ACL and
      security-related properties.
  5. Dump web assets with Assetic: `php app/console assetic:dump --env=prod --no-debug`
  6. Ensure that `web/uploads/images/` directory has write permissions.

In case of error, don't forget to clear de cache:

  * Development environment: `php app/console cache:clear --no-warmup`
  * Production environment: `php app/console cache:clear --no-warmup --env=prod`

## How to test ##

Cupon application includes several unit and functional tests. In order to run
the tests, you must have [PHPUnit](https://github.com/sebastianbergmann/phpunit/)
installed on your machine. Then, execute the following command on the project's
root directory:

~~~
$ phpunit -c app
~~~

## Frontend ##

  * URL:
    * Development environment: `http://coupon.local/app_dev.php`
    * Production environment: `http://coupon.local/app.php`
  * User credentials:
    * Login: `userN@localhost` being `N` an integer ranging from `1` to `500`
    * Password: `userN` being `N` the number used in login

## Extranet ##

  * URL:
    * Development environment: `http://coupon.local/app_dev.php/extranet`
    * Production environment: `http://coupon.local/app.php/extranet`
  * User credentials:
    * Login: `storeN` being `N` an integer ranging from `1` to `80` approximately
      (the upper bound is randomly generated)
    * Password: same as login

## Backend ##

  * URL:
    * Development environment: `http://coupon.local/app_dev.php/backend`
    * Production environment: `http://coupon.local/app.php/backend`
  * User credentials:
    * Login: `admin`
    * Password: `1234`
