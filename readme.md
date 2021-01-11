# Clue
A Laravel / MySQL game management application and player interface for the biannual, live-action Clue game at Wilson Library.

## Requirements
* [MySQL](https://dev.mysql.com/downloads/mysql/)
* PHP 7.3
* [Composer](https://getcomposer.org/)
* [npm](https://www.npmjs.com/)
* [Capistrano](http://capistranorb.com/) (for deployment)
* [Ruby](https://www.ruby-lang.org/en/) (for Capistrano)

## Installation
1. Get a fresh Clue MySQL production database dump from a UNC library sysadmin.
2. Create an empty MySQL database locally.
```bash
$ mysql -u username -p
mysql> create database clue
```

3. Import the MySQL dump.
```bash
$ mysql -u username clue < clue-sql-file.sql -p
```

4. Clone the repo to your local machine ($CLUE_HOME).
```bash
$ git clone https://github.com/UNC-Libraries/wilson-clue-app.git
```

5. Install Clue dependencies.
```bash
$ cd $CLUE_HOME
$ composer update #PHP dependencies
$ npm install
```

6. Create a new application key.
```bash
$ php artisan key:generate
```
7. Get the images from production to your local machine
```bash
$ rsync -rv production.server.com:/path/to/deploy/code/* $CLUE_HOME/storage/app/public
```

8. Symlink the public storage directory into the public directory
```bash
$ ln -nfs $CLUE_HOME/storage/app/public $CLUE_HOME/public/storage
```

## Configuration
1. Copy ```$CLUE_HOME/.env.example``` to ```$CLUE_HOME/.env```.
2. Edit the DB_\* and LDAP_\* properties in ```$CLUE_HOME/.env```.
3. To enable emails, set `MAIL_DRIVER` to `smtp` in ```$CLUE_HOME/.env```.

The DB_\* properties will be determined by you, the developer, based on your database configuration. 
The LDAP_\* properties you should get from a UNC sysadmin or from the production `.env` file.

## Asset Compilation
1. Watch for changes to assets in development mode:

   ```bash
   $ cd $CLUE_HOME
   $ npm run watch
   ```

1. Re-compile and minify assets before committing changes:

   ```bash
   $ cd $CLUE_HOME
   $ npm run production
   ```

## Running
1. Start the local PHP web server.

   ```bash
   $ cd $CLUE_HOME
   $ php artisan serve
   ```
   In your favorite web browser, go to http://localhost:8000/

---
For additional information about the application's structure and use, see the [Wiki](https://github.com/UNC-Libraries/wilson-clue-app/wiki)
   

