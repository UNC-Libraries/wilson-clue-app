# Clue
A Laravel / MySQL game management application and player interface for the biannual, live-action Clue game at Wilson Library.

## Requirements
* [MySQL](https://dev.mysql.com/downloads/mysql/)
* PHP 5.6
* [Composer](https://getcomposer.org/)
* [npm](https://www.npmjs.com/)
* [Capistrano](http://capistranorb.com/) (for deployment)
* [Ruby](https://www.ruby-lang.org/en/) (for Capistrano)

## Installation
1. Get a fresh Jitterbug MySQL production database dump from a UNC library sysadmin.
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
$ git clone git@gitlab.lib.unc.edu:cappdev/clue.git clue
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
$ rsync -rv iguana.lib.unc.edu:/net/deploy/prod/clue/shared/storage/app/public/* $CLUE_HOME/storage/app/public
```

8. Symlink the public storage directory into the public directory
```bash
$ ln -nfs $CLUE_HOME/storage/app/public $CLUE_HOME/public/storage
```

## Configuration
1. Copy ```$CLUE_HOME/.env.example``` to ```$CLUE_HOME/.env```.
2. Edit the DB_\* and ADLDAP_\* properties in ```$CLUE_HOME/.env```. 

The DB_\* properties will be determined by you, the developer, based on your database configuration. 
The ADLDAP_\* properites you should get from a UNC sysadmin or from the production `.env` file.

## Asset Compilation
1. Run Gulp

```bash
$ cd $CLUE_HOME
$ gulp
$ gulp watch
```

## Running
1. Start the local PHP web server.

```bash
$ cd $CLUE_HOME
$ php artisan serve
```
   In your favorite web browser, go to http://localhost:8000/

---
   

