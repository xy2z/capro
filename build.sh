#!/bin/bash

# Build php-scoper into /build directory.
# old 'composer build' command: "php-scoper add-prefix vendor src stubs tests .gitattributes .gitignore CONTRIBUTING.md capro composer.json composer.lock src\\helpers.php"

# Using "--no-dev" only to test phpunit files after build, delete it afterwards!
# composer install --no-dev --prefer-dist --no-interaction --no-progress --no-suggest --no-scripts --no-plugins --no-autoloader || exit
composer install --prefer-dist --no-interaction --no-progress --no-scripts --no-plugins --no-autoloader || exit

# rm -rf ./build # not needed with '--force'
# php-scoper add-prefix vendor src stubs tests .gitattributes .gitignore CONTRIBUTING.md capro composer.json composer.lock src\\helpers.php
# php-scoper add-prefix vendor src stubs tests .gitattributes .gitignore capro src\\helpers.php

# Includes composer.json just to do the "composer dump" command, it gets overwritten by "build-files/composer.json" afterwards.
# php-scoper add-prefix src stubs .gitattributes .gitignore capro vendor composer.json --force || exit
php-scoper add-prefix tests src stubs .gitattributes .gitignore capro vendor composer.json --force || exit
cd build || exit

composer dump --classmap-authoritative || exit
cp -a ../build-files/. . || exit

# Test
php capro -v || exit
php capro where || exit
php capro config || exit
php capro build || exit
echo ''
echo 'Done!'

# TODO: Maybe run phpunit tests, and then delete the test files if success, because it's not needed for the actual build (todo later)

